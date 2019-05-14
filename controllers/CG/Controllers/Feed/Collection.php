<?php
namespace CG\Controllers\Feed;

use CG\Feed\Filter;
use CG\Feed\RestService;
use CG\Slim\Controller\Collection\GetTrait;
use CG\Slim\Controller\Collection\PostTrait;
use CG\Slim\ControllerTrait;
use Slim\Slim;
use Zend\Di\Di;

class Collection
{
    use ControllerTrait, GetTrait, PostTrait;

    public function __construct(Slim $app, RestService $service, Di $di)
    {
        $this->setSlim($app)
            ->setService($service)
            ->setDi($di);
    }

    protected function getCollection()
    {
        return $this->getData();
    }

    public function getData()
    {
        return $this->getService()->fetchCollectionByFilterAsHal(
            new Filter(
                $this->getParams('limit') ?? null,
                $this->getParams('page') ?? null,
                $this->getParams('id') ?? [],
                $this->getParams('organisationUnitId') ?? [],
                $this->getParams('partnerId') ?? [],
                $this->getParams('createdDateFrom') ?? null,
                $this->getParams('createdDateTo') ?? null,
                $this->getParams('completedDateFrom') ?? null,
                $this->getParams('completedDateTo') ?? null,
                $this->getParams('status') ?? [],
                $this->getParams('statusCalculated') ?? null,
                $this->getParams('embedMessages') !== null ? filter_var($this->getParams('embedMessages'), FILTER_VALIDATE_BOOLEAN) : null
            )
        );
    }
}