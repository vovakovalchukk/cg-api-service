<?php
namespace CG\Feed\Message;

use CG\Feed\Message\Entity as Message;
use CG\Feed\Message\Filter;
use CG\Feed\Message\Service;
use CG\Slim\Renderer\ResponseType\Hal;

class RestService extends Service
{
    const DEFAULT_LIMIT = 10;
    const DEFAULT_PAGE = 1;
    const URL = '/feed/{feedId}/message';

    public function fetchCollectionByFilterAsHal(Filter $filter): Hal
    {
        if (!$filter->getPage()) {
            $filter->setPage(static::DEFAULT_PAGE);
        }
        if (!$filter->getLimit()) {
            $filter->setLimit(static::DEFAULT_LIMIT);
        }

        $collection = $this->getRepository()->fetchCollectionByFilter($filter);
        return $this->getMapper()->collectionToHal($collection, $this->getUrlFromFilter($filter), $filter->getLimit(), $filter->getPage());
    }

    protected function getUrlFromFilter(Filter $filter): string
    {
        if (empty($filter->getFeedId())) {
            throw new \InvalidArgumentException('Feed Message endpoint requires a Feed ID');
        }
        return str_replace('{feedId}', $filter->getFeedId()[0], static::URL);
    }
}