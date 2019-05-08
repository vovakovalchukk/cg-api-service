<?php
namespace CG\Feed;

use CG\Feed\Message\Filter as MessageFilter;
use CG\Feed\Message\Service as MessageService;
use CG\Slim\Renderer\ResponseType\Hal;

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
        $halEntity = parent::saveHal($hal, $ids);
        $resources = $hal->getResources();
        $data = $hal->getData();
        $messages = $resources['messages'] ?? $data['messages'] ?? [];
        foreach ($messages as $message) {
            $this->messageService->saveHal($message, ['id' => $message['id'] ?? null]);
        }
        return $halEntity;
    }
}