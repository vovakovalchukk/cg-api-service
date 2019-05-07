<?php
namespace CG\Root;

use CG\Slim\Renderer\ResponseType\Hal;
use Zend\Di\Di;

class Mapper
{
    protected $di;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getHal()
    {
        return $this->di->newInstance(Hal::class, ['uri' => '/'])
            ->addLink('webhook', '/webhook');
    }
}