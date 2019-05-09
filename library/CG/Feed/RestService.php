<?php
namespace CG\Feed;

use CG\Feed\Message\Filter as MessageFilter;
use CG\Feed\Message\Service as MessageService;
use Nocarrier\Hal;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;

    /** @var MessageService */
    protected $messageService;

    public function __construct(StorageInterface $repository, Mapper $mapper, MessageService $messageService)
    {
        parent::__construct($repository, $mapper);
        $this->messageService = $messageService;
    }

    public function fetchAsHal($id)
    {
        $entity = $this->fetch($id);
        //Converting to Collection removes need for duplicate code throughout the codebase
        $collection = new Collection(Entity::class, __FUNCTION__, ['id' => [$id]]);
        $collection->attach($entity);
        $this->fetchCollectionEmbeds($collection);
        return $this->getMapper()->toHal($entity);
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
        $collection = $this->fetchCollectionEmbeds($collection);
        return $this->getMapper()->collectionToHal($collection, '/feed', $filter->getLimit(), $filter->getPage());
    }

    protected function fetchCollectionEmbeds(Collection $collection): Collection
    {
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
        // Re-fetch so any new Messages are embedded
        return $this->fetchAsHal($entity->getId());
    }

    protected function setTotalMessageCountOnHal(Hal $hal): Hal
    {
        if (isset($data['totalMessageCount'])) {
            return $hal;
        }
        $data = $hal->getData();
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

    public function remove(Entity $entity)
    {
        foreach ($entity->getMessages() as $message) {
            $this->messageService->remove($message);
        }
        return parent::remove($entity);
    }
}