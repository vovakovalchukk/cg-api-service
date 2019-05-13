<?php

use CG\WebhookServer\Repository;
use CG\WebhookServer\StorageInterface;
use CG\WebhookServer\Storage\Db;
use CG\WebhookServer\Storage\Cache;
use CG\WebhookServer\Mapper;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                StorageInterface::class => Repository::class
            ],
            Repository::class => [
                'parameter' => [
                    'storage' => Cache::class,
                    'repository' => Db::class,
                ]
            ],
            Db::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => Mapper::class
                ]
            ]
        ]
    ]
];