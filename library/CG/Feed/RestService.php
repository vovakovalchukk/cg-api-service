<?php
namespace CG\Feed;

use CG\Feed\Gearman\Workload\ProcessFeed as ProcessFeedWorkload;
use CG\Feed\Message\Entity as Message;
use CG\Feed\Message\Filter as MessageFilter;
use CG\Feed\Message\Service as MessageService;
use DateTime;
use GearmanClient;
use Nocarrier\Hal;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var MessageService */
    protected $messageService;
    /** @var GearmanClient */
    protected $gearmanClient;

    public function __construct(
        StorageInterface $repository,
        Mapper $mapper,
        MessageService $messageService,
        GearmanClient $gearmanClient
    ) {
        parent::__construct($repository, $mapper);
        $this->messageService = $messageService;
        $this->gearmanClient = $gearmanClient;
    }

    public function fetchAsHal($id)
    {
        $entity = $this->fetch($id);
        $this->persistStatusIfComplete($entity);
        //Converting to Collection removes need for duplicate code throughout the codebase
        $collection = new Collection(Entity::class, __FUNCTION__, ['id' => [$id]]);
        $collection->attach($entity);
        $this->fetchCollectionEmbeds($collection);
        return $this->getMapper()->toHal($entity);
    }

    protected function persistStatusIfComplete(Entity $entity): Entity
    {
        if (!$entity->isStatusCalculated() || $entity->getStatus() !== Entity::STATUS_COMPLETE) {
            return $entity;
        }
        $entity->setStatusCalculated(false)
            ->setCompletedDate(new DateTime());
        $this->save($entity);
        return $entity;
    }

    public function fetchCollectionByFilterAsHal(Filter $filter): Hal
    {
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }

        $collection = $this->getRepository()->fetchCollectionByFilter($filter);
        $collection = $this->persistStatusesIfComplete($collection);
        $collection = $this->fetchCollectionEmbeds($collection, $filter);
        return $this->getMapper()->collectionToHal($collection, '/feed', $filter->getLimit(), $filter->getPage());
    }

    protected function persistStatusesIfComplete(Collection $collection): Collection
    {
        foreach ($collection as $entity) {
            $this->persistStatusIfComplete($entity);
        }
        return $collection;
    }

    protected function fetchCollectionEmbeds(Collection $collection, Filter $filter = null): Collection
    {
        if ($filter && !$filter->isEmbedMessages()) {
            return $collection;
        }
        try {
            $filter = new MessageFilter();
            $filter->setLimit('all')
                ->setPage(1)
                ->setFeedId($collection->getIds());
            $messages = $this->messageService->fetchCollectionByFilter($filter);
        } catch (NotFound $e) {
            //Ignore Not Found Errors
            return $collection;
        }

        foreach ($collection as $feed) {
            $feed->setMessages($messages->getBy('feedId', $feed->getId()));
        }

        return $collection;
    }

    public function saveHal(Hal $hal, array $ids)
    {
        $this->setTotalMessageCountOnHal($hal);
        $halEntity = parent::saveHal($hal, $ids);
        $entity = $this->mapper->fromHal($halEntity);
        $this->saveMessagesFromHal($hal, $entity);
        // Re-fetch so any new Messages are embedded and calculated fields are calculated
        $fetchedHal = $this->fetchAsHal($entity->getId());
        $this->createJobToProcess($this->mapper->fromHal($fetchedHal));
        return $fetchedHal;
    }

    protected function setTotalMessageCountOnHal(Hal $hal): Hal
    {
        $data = $hal->getData();
        if (isset($data['totalMessageCount'])) {
            return $hal;
        }
        $messagesData = $data['messages'] ?? [];
        $data['totalMessageCount'] = count($messagesData);
        $hal->setData($data);
        return $hal;
    }

    protected function saveMessagesFromHal(Hal $hal, Entity $entity): void
    {
        $data = $hal->getData();
        $messagesData = $data['messages'] ?? [];
        $index = 1;
        foreach ($messagesData as $messageData) {
            $messageData['feedId'] = $entity->getId();
            $messageData['organisationUnitId'] = $entity->getOrganisationUnitId();
            $messageData['index'] = $index++;

            $messageHal = new Hal(null, $messageData);
            $this->messageService->saveHal($messageHal, []);
        }
    }

    protected function createJobToProcess(Entity $entity): void
    {
        if ($entity->getStatus() != Message::STATUS_RECEIVED) {
            return;
        }
        $workload = new ProcessFeedWorkload($entity->getId());
        $unique = ProcessFeedWorkload::FUNCTION_NAME . '-' . $entity->getId();
        $this->gearmanClient->doBackground(ProcessFeedWorkload::FUNCTION_NAME, serialize($workload), $unique);
    }

    public function remove(Entity $entity)
    {
        foreach ($entity->getMessages() as $message) {
            $this->messageService->remove($message);
        }
        return parent::remove($entity);
    }
}