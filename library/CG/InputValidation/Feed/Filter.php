<?php
namespace CG\InputValidation\Feed;

use CG\Feed\Entity as Feed;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Validation\Rules\ArrayOfIntegersValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\IsArrayValidator;
use CG\Validation\Rules\PaginationTrait;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
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
            'createdDateFrom' => [
                'name' => 'createdDateFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => StdlibDateTime::FORMAT])
                ]
            ],
            'createdDateTo' => [
                'name' => 'createdDateTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => StdlibDateTime::FORMAT])
                ]
            ],
            'completedDateFrom' => [
                'name' => 'completedDateFrom',
                'required' => false,
                'validators' => [
                    new Date(['format' => StdlibDateTime::FORMAT])
                ]
            ],
            'completedDateTo' => [
                'name' => 'completedDateTo',
                'required' => false,
                'validators' => [
                    new Date(['format' => StdlibDateTime::FORMAT])
                ]
            ],
            'status' => [
                'name' => 'status',
                'required' => false,
                'validators' => [
                    new IsArrayValidator(['name' => 'status', 'haystack' => Feed::getAllStatuses()])
                ]
            ],
            'statusCalculated' => [
                'name' => 'status',
                'required' => false,
                'validators' => [new BooleanValidator(['name' => 'statusCalculated'])]
            ],
        ];

        return array_merge($this->getPaginationValidation(), $rules);
    }
}