<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration;

use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;

#[\Attribute(flags: \Attribute::IS_REPEATABLE + \Attribute::TARGET_PROPERTY)]
readonly class JoinMetadata
{
    /**
    * @param string $joinExpression
    * Field that should be joined on:
    * Either just the property (then it will use the current entity)
    * Or a foreign entity and its property (user.address)
    */
    public function __construct(
        public string $joinExpression,
        public string $joinAlias,
        public ?string $joinCondition = null,
        public string $joinMethod = DoctrineJoinDescriptor::JOIN_METHOD_INNER,
        public string $joinConditionMethod = DoctrineJoinDescriptor::JOIN_CONDITION_METHOD_WITH,
    ) {
    }
}
