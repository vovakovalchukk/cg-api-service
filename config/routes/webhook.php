<?php

use CG\Controllers\Webhook\Collection as CollectionController;
use CG\Controllers\Webhook\Entity as EntityController;
use CG\InputValidation\Webhook\Entity as EntityValidation;
use CG\InputValidation\Webhook\Filter as FilterValidation;
use CG\WebhookServer\Entity as Webhook;
use CG\WebhookServer\Mapper as WebhookMapper;
use CG\WebhookServer\RestService as WebhookService;
use CG\Slim\Versioning\Version;

return [
    '/webhook' => [
        'controllers' => function() use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(CollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        'via' => ['GET', 'POST', 'OPTIONS'],
        'name' => 'WebhookCollection',
        'entityRoute' => '/webhook/:webhookId',
        'validation' => [
            'filterRules' => FilterValidation::class,
            'dataRules' => EntityValidation::class
        ],
        'version' => new Version(1, 1)
    ],
    '/webhook/:webhookId' => [
        'controllers' => function($webhookId) use ($di, $app) {
            $method = $app->request()->getMethod();
            $controller = $di->get(EntityController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($webhookId, $app->request()->getBody())
            );
        },
        'via' => ['GET', 'PUT', 'DELETE', 'OPTIONS'],
        'name' => 'WebhookEntity',
        'validation' => [
            'dataRules' => EntityValidation::class,
        ],
        'version' => new Version(1, 1),
        'eTag' => [
            'mapperClass' => WebhookMapper::class,
            'entityClass' => Webhook::class,
            'serviceClass' => WebhookService::class
        ]
    ],
];
