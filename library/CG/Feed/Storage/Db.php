<?php
namespace CG\Feed\Storage;

use CG\Feed\Collection;
use CG\Feed\Entity;
use CG\Feed\Filter;
use CG\Feed\StorageInterface;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Predicate\Operator;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'feed';

    protected $calculatedFields = ['successfulMessageCount', 'failedMessageCount'];

    public function fetchCollectionByFilter(Filter $filter)
    {
        try {
            $query = $this->buildFilterQuery($filter);
            $select = $this->getSelect()->where($query);

            if ($filter->getLimit() != 'all') {
                $offset = ($filter->getPage() - 1) * $filter->getLimit();
                $select->limit($filter->getLimit())
                    ->offset($offset);
            }

            return $this->fetchPaginatedCollection(
                new Collection($this->getEntityClass(), __FUNCTION__, $filter->toArray()),
                $this->getReadSql(),
                $select,
                $this->getMapper()
            );
        } catch (ExceptionInterface $e) {
            throw new StorageException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildFilterQuery(Filter $filter)
    {
        $query = [];

        if (!empty($filter->getId())) {
            $query['id'] = $filter->getId();
        }
        if (!empty($filter->getOrganisationUnitId())) {
            $query['organisationUnitId'] = $filter->getOrganisationUnitId();
        }
        if (!empty($filter->getPartnerId())) {
            $query['partnerId'] = $filter->getPartnerId();
        }
        if (!empty($filter->getCreatedDateFrom())) {
            $query[] = new Operator('feed.createdDate', Operator::OP_GTE, $filter->getCreatedDateFrom());
        }
        if (!empty($filter->getCreatedDateTo())) {
            $query[] = new Operator('feed.createdDate', Operator::OP_LTE, $filter->getCreatedDateTo());
        }
        if (!empty($filter->getCompletedDateFrom())) {
            $query[] = new Operator('feed.completedDate', Operator::OP_GTE, $filter->getCompletedDateFrom());
        }
        if (!empty($filter->getCompletedDateTo())) {
            $query[] = new Operator('feed.completedDate', Operator::OP_LTE, $filter->getCompletedDateTo());
        }
        if (!empty($filter->getStatus())) {
            $query['status'] = $filter->getStatus();
        }
        if ($filter->getStatusCalculated() !== null) {
            $query['statusCalculated'] = $filter->getStatusCalculated();
        }

        return $query;
    }

    protected function getEntityArray($entity)
    {
        $array = $entity->toArray();
        $calculatedFields = $this->calculatedFields;
        foreach ($calculatedFields as $calculatedField) {
            unset($array[$calculatedField]);
        }
        if ($entity->isStatusCalculated()) {
            $array['status'] = Entity::STATUS_CALCULATED;
        }
        return $array;
    }

    protected function getSelect()
    {
        return $this->getReadSql()->select(static::DB_TABLE_NAME);
    }

    protected function getInsert()
    {
        return $this->getWriteSql()->insert(static::DB_TABLE_NAME);
    }

    protected function getUpdate()
    {
        return $this->getWriteSql()->update(static::DB_TABLE_NAME);
    }

    protected function getDelete()
    {
        return $this->getWriteSql()->delete(static::DB_TABLE_NAME);
    }

    public function getEntityClass()
    {
        return Entity::class;
    }
}