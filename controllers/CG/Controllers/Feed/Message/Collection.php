<?php
namespace CG\Controllers\Feed\Message;

use CG\Feed\Message\Filter;
use CG\Feed\Message\RestService;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait, GetTrait;

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    public function get($feedId)
    {
        try {
            return $this->getData($feedId);
        } catch (NotFound $e) {
            throw new HttpNotFound($e->getMessage(), $e->getCode(),$e);
        } catch (PermissionException $e) {
            throw new HttpNotFound('Collection Not Found', $e->getCode(), $e);
        }
    }

    public function getData($feedId)
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            new Filter(
                $this->getParams('limit') ?? null,
                $this->getParams('page') ?? null,
                [$feedId],
                $this->getParams('index') ?? [],
                $this->getParams('type') ?? [],
                $this->getParams('status') ?? []
            )
        );
    }
}