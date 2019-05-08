<?php
namespace CG\InputValidation\Feed;

use CG\Feed\Entity as Feed;
use CG\InputValidation\Feed\Message\Entity as FeedMessageRules;
use CG\Stdlib\DateTime as StdlibDateTime;
use CG\Validation\Rules\ArrayOfEntitiesValidator;
use CG\Validation\Rules\BooleanValidator;
use CG\Validation\Rules\IntegerValidator;
use CG\Validation\Rules\ValidatorTrait;
use CG\Validation\RulesInterface;
use Zend\Di\Di;
use Zend\Validator\Date;
use Zend\Validator\GreaterThan;
use Zend\Validator\InArray;

class Entity implements RulesInterface
{
    use ValidatorTrait;

    /** @var FeedMessageRules */
    protected $feedMessageRules;

    public function __construct(Di $di, FeedMessageRules $feedMessageRules)
    {
        $this->di = $di;
        $this->feedMessageRules = $feedMessageRules;
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
            'createdDate' => [
                'name'       => 'createdDate',
                'required'   => true,
                'validators' => [new Date(['format' => StdlibDateTime::FORMAT])]
            ],
            'completedDate' => [
                'name'       => 'completedDate',
                'required'   => false,
                'validators' => [new Date(['format' => StdlibDateTime::FORMAT])]
            ],
            'status' => [
                'name' => 'status',
                'required' => true,
                'validators' => [(new InArray())->setHaystack(Feed::getAllStatuses())]
            ],
            'statusCalculated' => [
                'name' => 'status',
                'required' => true,
                'validators' => [new BooleanValidator(['name' => 'statusCalculated'])]
            ],
            'totalMessageCount' => [
                'name' => 'totalMessageCount',
                'required' => true,
                'validators' => [new IntegerValidator(['name' => 'totalMessageCount'])]
            ],
            'successfulMessageCount' => [
                'name' => 'successfulMessageCount',
                'required' => false,
                'validators' => [new IntegerValidator(['name' => 'successfulMessageCount'])]
            ],
            'failedMessageCount' => [
                'name' => 'failedMessageCount',
                'required' => false,
                'validators' => [new IntegerValidator(['name' => 'failedMessageCount'])]
            ],
            'messages' => [
                'name' => 'messages',
                'required' => false,
                'validators' => [new ArrayOfEntitiesValidator($this->feedMessageRules, 'messages')]
            ]
        ];
    }
}