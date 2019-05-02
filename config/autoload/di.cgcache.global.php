<?php
use CG\Cache\InvalidationHandler;

return [
    'di' => [
        'instance' => [
            InvalidationHandler::class => [
                'parameters' => [
                    'validateCollectionChance' => [
                        InvalidationHandler::VALIDATE_COLLECTION_CHANCE_DEFAULT_KEY => 50,
                    ],
                ],
            ],
        ],
    ]
];
