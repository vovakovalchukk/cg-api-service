<?php
namespace CG\WebhookServer\Storage;

use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use CG\WebhookServer\Collection;
use CG\WebhookServer\Entity;
use CG\WebhookServer\Filter;
use CG\WebhookServer\StorageInterface;
use Zend\Db\Sql\Exception\ExceptionInterface;
use Zend\Db\Sql\Predicate\Expression;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'webhook';

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
        if (!empty($filter->getAccountId())) {
            $accountIds = $filter->getAccountId();
            if (!in_array(Entity::NO_ACCOUNT_ID, $accountIds)) {
                $query['accountId'] = $filter->getAccountId();
            } elseif (count($accountIds) == 1) {
                $query[] = new Expression('accountID IS NULL');
            } else {
                $accountIds = array_diff($accountIds, [Entity::NO_ACCOUNT_ID]);
                $query[] = new Expression('(accountId IS NULL OR accountId IN ('.implode(', ', $accountIds).'))');
            }
        }
        if (!empty($filter->getType())) {
            $query['type'] = $filter->getType();
        }
        if (!empty($filter->getAction())) {
            $query['action'] = $filter->getAction();
        }
        if (!empty($filter->getStatus())) {
            $query['status'] = $filter->getStatus();
        }

        return $query;
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