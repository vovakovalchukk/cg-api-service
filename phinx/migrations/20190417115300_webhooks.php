<?php

use Phinx\Migration\AbstractMigration;

class Webhooks extends AbstractMigration
{
    public function change()
    {
        $this
            ->table('webhook', ['row_format' => 'COMPRESSED'])
            ->addColumn('organisationUnitId', 'integer', ['null' => false])
            ->addColumn('partnerId', 'integer', ['null' => true])
            ->addColumn('accountId', 'integer', ['null' => true])
            ->addColumn('type', 'string', ['null' => false])
            ->addColumn('action', 'string', ['null' => true])
            ->addColumn('url', 'string', ['null' => false])
            ->addColumn('createdDate', 'datetime', ['null' => false])
            ->addColumn('updatedDate', 'datetime', ['null' => false])
            ->addColumn('status', 'string', ['null' => false])
            ->addIndex(['organisationUnitId', 'type', 'status'])
            ->addIndex(['organisationUnitId', 'partnerId'])
            ->create();
    }
}