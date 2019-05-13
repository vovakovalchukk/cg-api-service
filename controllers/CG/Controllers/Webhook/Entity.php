<?php
namespace CG\Controllers\Webhook;

use CG\Slim\Controller\Entity\DeleteTrait;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use CG\WebhookServer\RestService;
use Slim\Slim;
use Zend\Di\Di;

class Entity
{
    use ControllerTrait;
    use GetTrait;
    use PutTrait;
    use DeleteTrait;

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }
}