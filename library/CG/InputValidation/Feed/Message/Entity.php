<?php
namespace CG\InputValidation\Feed\Message;

use CG\Feed\Message\Entity as Message;
use CG\Validation\RequestMethodAwareInterface;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\GreaterThan;
use Zend\Validator\InArray;
use Zend\Validator\StringLength;

class Entity implements RulesInterface, RequestMethodAwareInterface
{
    use ValidatorTrait;

    /** @var string */
    protected $requestMethod;

    public function __construct(Di $di)
    {
        $this->di = $di;
    }

    public function getRules()
    {
        return [
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => $this->requiredAfterPost(),
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'feedId' => [
                'name' => 'feedId',
                'required' => $this->requiredAfterPost(),
                'validators' => [
                    new IntegerValidator(['name' => 'feedId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'feedId must be at least %min%'])
                ]
            ],
            'index' => [
                'name' => 'index',
                'required' => $this->requiredAfterPost(),
                'validators' => [
                    new IntegerValidator(['name' => 'index']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'index must be at least %min%'])
                ]
            ],
            'type' => [
                'name'       => 'type',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'payload' => [
                'name'       => 'payload',
                'required'   => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'status' => [
                'name' => 'status',
                'required' => false,
                'validators' => [(new InArray())->setHaystack(Message::getAllStatuses())]
            ],
            'errorMessage' => [
                'name'       => 'errorMessage',
                'required'   => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
        ];
    }

    protected function requiredAfterPost(): bool
    {
        return ($this->requestMethod != 'POST');
    }

    public function setRequestMethod($requestMethod)
    {
        $this->requestMethod = $requestMethod;
    }
}