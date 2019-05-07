<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

use CG\Cache\Client\Redis as CacheRedis;
use CG\Cache\InvalidationHandler;
use CG\ETag\Storage\Predis;
use CG\ETag\Storage\Predis as EtagRedis;
use CG\ETag\StorageInterface;
use CG\Log\Shared\Storage\Redis\Channel as RedisChannel;
use CG\OrganisationUnit\Service as OrganisationUnitService;
use CG\OrganisationUnit\Storage\Api as OrganisationUnitStorageApi;
use CG\Transaction\Client\Redis as TransactionRedisClient;
use CG\Transaction\ClientInterface as TransactionClientInterface;
use CG\Transaction\LockInterface as LockClientInterface;
use CG\Usage\Aggregate\Storage\Db as UsageAggregateDb;
use CG\Usage\Repository as UsageRepository;
use CG\Usage\Storage\Db as UsageDb;
use CG\Usage\Storage\Redis as UsageRedis;
use CG\Usage\StorageInterface as UsageStorageInterface;
use CG\Zend\Stdlib\Cache\EventManager;
use Zend\Db\Sql\Sql;

$config = [
    'di' => [
        'instance' => [
            'aliases' => [
                'ReadSql' => Sql::class,
                'FastReadSql' => Sql::class,
                'WriteSql' => Sql::class,
            ],
            'preferences' => [
                'CG\Cache\ClientInterface' => 'CG\Cache\Client\Redis',
                'CG\Cache\IncrementInterface' => 'CG\Cache\Client\Redis',
                'CG\Cache\ClientPipelineInterface' => 'CG\Cache\Client\RedisPipeline',
                'CG\Cache\KeyGeneratorInterface' => 'CG\Cache\KeyGenerator\Redis',
                'CG\Cache\Strategy\SerialisationInterface' => 'CG\Cache\Strategy\Serialisation\Serialize',
                'CG\Cache\Strategy\CollectionInterface' => 'CG\Cache\Strategy\Collection\Entities',
                'CG\Cache\Strategy\EntityInterface' => 'CG\Cache\Strategy\Entity\Standard',
                StorageInterface::class => Predis::class,
                UsageStorageInterface::class => UsageRepository::class,
                LockClientInterface::class => TransactionRedisClient::class,
                TransactionClientInterface::class => TransactionRedisClient::class
            ],
            'ReadSql' => [
                'parameter' => [
                    'adapter' => 'readAdapter'
                ]
            ],
            'FastReadSql' => [
                'parameter' => [
                    'adapter' => 'fastReadAdapter'
                ]
            ],
            'WriteSql' => [
                'parameter' => [
                    'adapter' => 'writeAdapter'
                ]
            ],
            CacheRedis::class => [
                'parameter' => [
                    'predis' => 'unreliable_redis_deferred',
                    'globalEventManager' => EventManager::class
                ]
            ],
            EtagRedis::class => [
                'parameter' => [
                    'predisClient' => 'unreliable_redis_deferred'
                ]
            ],
            UsageDb::class => [
                'parameter' => [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                ]
            ],
            UsageAggregateDb::class => [
                'parameter'=> [
                    'readSql' => 'ReadSql',
                    'fastReadSql' => 'FastReadSql',
                    'writeSql' => 'WriteSql'
                ]
            ],
            UsageRepository::class => [
                'parameter' => [
                    'storage' => UsageRedis::class,
                    'repository' => UsageDb::class
                ]
            ],
            UsageRedis::class => [
                'parameter' => [
                    'client' => 'unreliable_redis',
                    'aggregateStorage' => UsageAggregateDb::class
                ]
            ],
            OrganisationUnitService::class => [
                'parameters' => [
                    'repository' => OrganisationUnitStorageApi::class,
                ]
            ],
            OrganisationUnitStorageApi::class => [
                'parameters' => [
                    'client' => 'directory_guzzle',
                ]
            ],
            RedisChannel::class => [
                'parameters' => [
                    'rootOrganisationUnitProvider' => OrganisationUnitService::class
                ]
            ],
            InvalidationHandler::class => [
                'parameters' => [
                    'eventManager' => EventManager::class
                ]
            ],
        ]
    ]
];

$configFiles = glob(__DIR__ . '/global/*.php');
foreach ($configFiles as $configFile) {
    $configFileContents = require_once $configFile;
    $config = \Zend\Stdlib\ArrayUtils::merge($config, $configFileContents);
}
return $config;