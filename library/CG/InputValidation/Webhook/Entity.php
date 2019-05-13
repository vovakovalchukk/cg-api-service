<?php
namespace CG\InputValidation\Webhook;

use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use CG\WebhookServer\Entity as Webhook;
use Zend\Di\Di;
use Zend\Validator\Date;
use Zend\Validator\GreaterThan;
use Zend\Validator\InArray;
use Zend\Validator\StringLength;
use Zend\Validator\Uri;

class Entity implements RulesInterface
{
    use ValidatorTrait;

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
                'validators' => [
                    new IntegerValidator(['name' => 'id']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'id must be at least %min%'])
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => true,
                'validators' => [
                    new IntegerValidator(['name' => 'organisationUnitId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'organisationUnitId must be at least %min%'])
                ]
            ],
            'partnerId' => [
                'name' => 'partnerId',
                'required' => false,
                'validators' => [
                    new IntegerValidator(['name' => 'partnerId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'partnerId must be at least %min%'])
                ]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => false,
                'validators' => [
                    new IntegerValidator(['name' => 'accountId']),
                    (new GreaterThan(['min' => 1, 'inclusive' => true]))
                        ->setMessages(['notGreaterThanInclusive' => 'accountId must be at least %min%'])
                ]
            ],
            'type' => [
                'name' => 'type',
                'required' => true,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'action' => [
                'name' => 'action',
                'required' => false,
                'validators' => [new StringLength(['min' => 1])]
            ],
            'url' => [
                'name' => 'url',
                'required' => true,
                'validators' => [new Uri(['allowRelative' => false])]
            ],
            'createdDate' => [
                'name'       => 'createdDate',
                'required'   => false,
                'validators' => [new Date(['format' => StdlibDateTime::FORMAT])]
            ],
            'updatedDate' => [
                'name'       => 'updatedDate',
                'required'   => false,
                'validators' => [new Date(['format' => StdlibDateTime::FORMAT])]
            ],
            'status' => [
                'name' => 'status',
                'required' => true,
                'validators' => [(new InArray())->setHaystack(Webhook::getAllStatuses())]
            ],
        ];
    }
}