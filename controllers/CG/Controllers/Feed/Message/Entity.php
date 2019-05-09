<?php
namespace CG\Controllers\Feed\Message;

use CG\Feed\Message\IdParts;
use CG\Feed\Message\RestService;
use CG\Slim\Controller\Entity\GetTrait;
use CG\Slim\Controller\Entity\PutTrait;
use CG\Slim\ControllerTrait;
use Nocarrier\Hal;
use Slim\Slim;
use Zend\Di\Di;

class Entity
{
    use ControllerTrait;
    use GetTrait {
        get as traitGet;
    }
    use PutTrait {
        put as traitPut;
    }

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($feedId, $index)
    {
        $idParts = IdParts::fromArray(['feedId' => $feedId, 'index' => $index]);
        return $this->traitGet($idParts->toId());
    }

    public function put($feedId, $index, Hal $hal)
    {
        $idParts = IdParts::fromArray(['feedId' => $feedId, 'index' => $index]);
        return $this->traitPut($idParts->toId(), $hal);
    }
}