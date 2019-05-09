<?php
namespace CG\Feed\Message\Storage;

use CG\Feed\Message\Collection;
use CG\Feed\Message\Entity;
use CG\Feed\Message\Filter;
use CG\Feed\Message\IdParts;
use CG\Feed\Message\StorageInterface;
use CG\Stdlib\Exception\Runtime\NotFound;
use CG\Stdlib\Exception\Storage as StorageException;
use CG\Stdlib\Storage\Collection\SaveInterface as SaveCollectionInterface;
use CG\Stdlib\Storage\Db\DbAbstract;
use Zend\Db\Sql\Exception\ExceptionInterface;

class Db extends DbAbstract implements StorageInterface, SaveCollectionInterface
{
    const DB_TABLE_NAME = 'feedMessage';

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

        if (!empty($filter->getFeedId())) {
            $query['feedId'] = $filter->getFeedId();
        }
        if (!empty($filter->getIndex())) {
            $query['index'] = $filter->getIndex();
        }
        if (!empty($filter->getType())) {
            $query['type'] = $filter->getType();
        }
        if (!empty($filter->getStatus())) {
            $query['status'] = $filter->getStatus();
        }

        return $query;
    }

    public function fetch($id)
    {
        $idParts = IdParts::fromId($id);
        return $this->fetchEntity(
            $this->getReadSql(),
            $this->getSelect()->where([
                'feedId' => $idParts->getFeedId(),
                'index' => $idParts->getIndex()
            ]),
            $this->getMapper()
        );
    }

    protected function saveEntity($entity)
    {
        try {
            $this->fetch($entity->getId());
            $this->updateEntity($entity);
        } catch (NotFound $e) {
            $this->insertEntity($entity);
        }
        return $entity;
    }

    protected function insertEntity($entity)
    {
        $insert = $this->getInsert()->values($this->getEntityArray($entity));
        $this->getWriteSql()->prepareStatementForSqlObject($insert)->execute();
        $entity->setNewlyInserted(true);
    }

    protected function updateEntity($entity)
    {
        $update = $this->getUpdate()->set($this->getEntityArray($entity))
            ->where(['feedId' => $entity->getFeedId(), 'index' => $entity->getIndex()]);
        $this->getWriteSql()->prepareStatementForSqlObject($update)->execute();
    }

    public function remove($entity)
    {
        $delete = $this->getDelete()->where(array(
            'feedId' => $entity->getFeedId(),
            'index' => $entity->getIndex()
        ));
        $this->getWriteSql()->prepareStatementForSqlObject($delete)->execute();
    }

    protected function getEntityArray($entity)
    {
        $array = $entity->toArray();
        unset($array['id']);
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