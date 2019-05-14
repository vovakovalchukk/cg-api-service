<?php
namespace CG\InputValidation\Feed\Message;

use CG\Feed\Message\Entity as Message;
use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use Zend\Di\Di;

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
            'index' => [
                'name' => 'index',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'index')
                ]
            ],
            'type' => [
                'name' => 'type',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'type'])
                ]
            ],
            'status' => [
                'name' => 'status',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'status', 'haystack' => Message::getAllStatuses()])
                ]
            ],
            'organisationUnitId' => [
                'name' => 'organisationUnitId',
                'required' => false,
                'validators' => [
                    new ArrayOfIntegersValidator(new IntegerValidator(), 'organisationUnitId')
                ]
            ],
        ];

        return array_merge($this->getPaginationValidation(), $rules);
    }
}