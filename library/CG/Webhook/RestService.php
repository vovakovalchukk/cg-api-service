<?php
namespace CG\WebhookServer;

use CG\Slim\Renderer\ResponseType\Hal;
use CG\WebhookServer\Entity as Webhook;
use CG\WebhookServer\Filter;
use CG\WebhookServer\Service;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;
    const DEFAULT_STATUSES = [Webhook::STATUS_ACTIVE];

    public function fetchCollectionByFilterAsHal(Filter $filter): Hal
    {
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }
        if (!$filter->getStatus()) {
            $filter->setStatus(static::DEFAULT_STATUSES);
        }
        $collection = $this->getRepository()->fetchCollectionByFilter($filter);
        return $this->getMapper()->collectionToHal($collection, '/webhook', $filter->getLimit(), $filter->getPage());
    }
}