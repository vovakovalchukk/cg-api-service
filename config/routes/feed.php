<?php

use CG\Controllers\Feed\Feed\Collection as FeedCollectionController;
use CG\Controllers\Feed\Feed as FeedController;
use CG\Controllers\Feed\Message\Collection as MessageCollectionController;
use CG\Controllers\Feed\Message as MessageController;
use CG\Feed\Entity as Feed;
use CG\Feed\Mapper;
use CG\Feed\Message\Entity as Message;
use CG\Feed\Message\Mapper as MessageMapper;
use CG\Feed\Message\RestService as MessageRestService;
use CG\Feed\RestService;
use CG\InputValidation\Feed\Entity as ValidationEntity;
use CG\InputValidation\Feed\Filter as ValidationFilter;
use CG\InputValidation\Feed\Message\Entity as ValidationMessageEntity;
use CG\InputValidation\Feed\Message\Filter as ValidationMessageFilter;
use CG\Slim\Versioning\Version;

return [
    "/feed" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationEntity::class,
            "filterRules" => ValidationFilter::class,
        ],
        "controllers" => function() use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(FeedCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        "via" => [
            'GET','POST','OPTIONS'
        ],
        'entityRoute' => '/feed/:id',
        "name" => "FeedCollection",
        "version" => new Version(1, 1)
    ],
    "/feed/:id" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationEntity::class,
            "filterRules" => null
        ],
        "controllers" => function($id) use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(FeedController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($id, $app->request()->getBody())
            );
        },
        "via" => [
            'GET','PUT','DELETE','OPTIONS'
        ],
        "name" => "FeedEntity",
        "version" => new Version(1, 1),
        'eTag' => [
            'mapperClass' => Mapper::class,
            'entityClass' => Feed::class,
            'serviceClass' => RestService::class
        ]
    ],
    "/feed/:id/message" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationMessageEntity::class,
            "filterRules" => ValidationMessageFilter::class,
        ],
        "controllers" => function() use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(MessageCollectionController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($app->request()->getBody())
            );
        },
        "via" => [
            'GET', 'OPTIONS'
        ],
        'entityRoute' => '/feed/:id/message/:messageId',
        "name" => "FeedMessageCollection",
        "version" => new Version(1, 1)
    ],
    "/feed/:id/message/:messageId" => [
        "validation" => [
            "flatten" => false,
            "dataRules" => ValidationMessageEntity::class,
            "filterRules" => null
        ],
        "controllers" => function($id) use ($di, $app) {
            $method = $app->request()->getMethod();

            $controller = $di->get(MessageController::class);
            $app->view()->set(
                'RestResponse',
                $controller->$method($id, $app->request()->getBody())
            );
        },
        "via" => [
            'GET', 'PUT', 'OPTIONS'
        ],
        "name" => "FeedEntity",
        "version" => new Version(1, 1),
        'eTag' => [
            'mapperClass' => MessageMapper::class,
            'entityClass' => Message::class,
            'serviceClass' => MessageRestService::class
        ]
    ],
];