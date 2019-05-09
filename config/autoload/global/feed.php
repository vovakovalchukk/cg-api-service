<?php

use CG\Feed\Message\Entity as Message;
use CG\Feed\Message\Mapper as MessageMapper;
use CG\Feed\Message\Repository as MessageRepository;
use CG\Feed\Message\Storage\Cache as MessageStorageCache;
use CG\Feed\Message\Storage\Db as MessageStorageDb;
use CG\Feed\Message\StorageInterface as MessageStorage;

use CG\Feed\Entity as Feed;
use CG\Feed\Mapper as FeedMapper;
use CG\Feed\Repository as FeedRepository;
use CG\Feed\Storage\Cache as FeedStorageCache;
use CG\Feed\Storage\Db as FeedStorageDb;
use CG\Feed\StorageInterface as FeedStorage;

use CG\Cache\InvalidationHandler;

return [
    'di' => [
        'instance' => [
            'preferences' => [
                FeedStorage::class => FeedRepository::class,
                MessageStorage::class => MessageRepository::class,
            ],
            FeedRepository::class => [
                'parameter' => [
                    'storage' => FeedStorageCache::class,
                    'repository' => FeedStorageDb::class,
                ]
            ],
            FeedStorageDb::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => FeedMapper::class
                ]
            ],
            MessageRepository::class => [
                'parameter' => [
                    'storage' => MessageStorageCache::class,
                    'repository' => MessageStorageDb::class,
                ]
            ],
            MessageStorageDb::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql',
                    'mapper' => MessageMapper::class
                ]
            ],
            InvalidationHandler::class => [
                'parameters' => [
                    'relationships' => [
                        Feed::class => [
                            ['entityClass' => Message::class]
                        ]
                    ]
                ]
            ]
        ]
    ]
];