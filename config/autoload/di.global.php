<?php
use CG\Di\Definition\CacheDefinition;
use CG\Di\DefinitionList;
use CG\Di\Di;
use Zend\Config\Config as ZendConfig;
use Zend\Db\Adapter\Adapter;
use Zend\Di\Config;
use Zend\Di\Definition\ClassDefinition;
use Zend\Di\Di as ZendDi;
use Zend\Di\InstanceManager;
use Zend\Di\LocatorInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

return [
    'service_manager' => [
        'factories' => [
            Di::class => function(ServiceLocatorInterface $serviceManager) {
                /** @var $configuration array */
                $configuration = $serviceManager->get('config');

                $runtimeDefinition = new CacheDefinition(
                    $introspectionStrategy = null,
                    $explicitClasses = (require dirname(dirname(__DIR__)) . '/vendor/composer/autoload_classmap.php'),
                    $cachePrefix = (ENVIRONMENT !== 'dev' ? $configuration['application_name'] ?? null : null)
                );

                $definitionList = new DefinitionList([$runtimeDefinition]);
                $im = new InstanceManager();
                $config = new Config($configuration['di'] ?? []);

                $di = new Di($definitionList, $im, $config);
                $di->definitions()->unshift(
                    (new ClassDefinition(\Memcached::class))->addMethodParameter('__construct', 'persistent_id', ['required' => false])
                );

                if (isset($configuration['db'], $configuration['db']['adapters'])) {
                    foreach (array_keys($configuration['db']['adapters']) as $adapter) {
                        $im->addAlias($adapter, Adapter::class);
                        $im->addSharedInstance($serviceManager->get($adapter), $adapter);
                    }
                }

                $im->addSharedInstance($di, Di::class);
                $im->addSharedInstance($di, ZendDi::class);
                $im->addSharedInstance($serviceManager, ServiceManager::class);
                $im->addSharedInstance($di->get('config', array('array' => $configuration)), 'config');
                $im->addSharedInstance($di->get(ZendConfig::class, array('array' => $configuration)), 'app_config');

                return $di;
            },
            ZendDi::class => function(ServiceLocatorInterface $serviceManager) {
                return $serviceManager->get(Di::class);
            }
        ],
        'shared' => [
            Di::class => true,
            ZendDi::class => true,
        ],
        'aliases' => [
            'Di' => Di::class,
        ],
    ],
    'di' => [
        'instance' => [
            'aliases' => [
                'Di' => Di::class,
                'config' => ZendConfig::class,
                'app_config' => ZendConfig::class,
            ],
            'preferences' => [
                ZendDi::class => Di::class,
                LocatorInterface::class => Di::class,
            ],
        ],
    ],
];