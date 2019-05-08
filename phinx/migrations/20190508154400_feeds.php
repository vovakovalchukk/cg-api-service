<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter as Adapter;

class Feeds extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('feed', ['row_format' => 'COMPRESSED'])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('partnerId', 'integer', ['null' => true])
            ->addColumn('createdDate', 'datetime', ['null' => false])
            ->addColumn('completedDate', 'datetime', ['null' => true])
            ->addColumn('status', 'string', ['null' => false])
            ->addColumn('statusCalculated', 'boolean', ['null' => false])
            ->addColumn('totalMessageCount', 'integer', ['null' => false])
            ->addIndex(['organisationUnitId', 'status'])
            ->addIndex(['organisationUnitId', 'partnerId'])
            ->addIndex(['organisationUnitId', 'createdDate'])
            ->addIndex(['organisationUnitId', 'completedDate'])
            ->create();

        $this
            ->table('feedMessage', ['row_format' => 'COMPRESSED', 'id' => false, 'primary_key' => ['feedId', 'index']])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('feedId', 'integer', ['null' => false])
            ->addColumn('index', 'integer', ['null' => false])
            ->addColumn('type', 'string', ['null' => false])
            ->addColumn('payload', 'string', ['null' => false, 'limit' => Adapter::TEXT_MEDIUM])
            ->addColumn('status', 'string', ['null' => false])
            ->addColumn('errorMessage', 'string', ['null' => true])
            ->addForeignKey('feedId', 'feed', 'id', ['delete' => 'CASCADE', 'update' => 'NOACTION'])
            ->addIndex(['feedId', 'type', 'status'])
            ->create();
    }
}