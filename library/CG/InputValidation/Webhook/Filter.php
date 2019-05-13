<?php
namespace CG\InputValidation\Webhook;

use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use CG\WebhookServer\Entity as Webhook;
use Zend\Di\Di;
use Zend\Validator\Date;

class Filter implements RulesInterface
{
    use ValidatorTrait;
    use PaginationTrait;

    public function __construct(Di $di)
    {
        $this->setDi($di);
    }

    public function getRules()
    {
        $rules = [
            'id' => [
                'name' => 'id',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'id')
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
            'partnerId' => [
                'name' => 'partnerId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'partnerId')
                ]
            ],
            'accountId' => [
                'name' => 'accountId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'accountId')
                ]
            ],
            'type' => [
                'name' => 'type',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'type'])
                ]
            ],
            'action' => [
                'name' => 'action',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'action'])
                ]
            ],
            'status' => [
                'name' => 'status',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'status', 'haystack' => Webhook::getAllStatuses()])
                ]
            ],
        ];

        return array_merge($this->getPaginationValidation(), $rules);
    }
}