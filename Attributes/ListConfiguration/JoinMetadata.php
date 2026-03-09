<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration;

use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;

#[\Attribute]
readonly class JoinMetadata
{
    public function __construct(
        public string $name,
        public ?string $entityClass,
        public ?string $join,
        public ?string $joinCondition,
        public string $joinMethod = DoctrineJoinDescriptor::JOIN_METHOD_INNER,
        public string $joinConditionMethod = DoctrineJoinDescriptor::JOIN_CONDITION_METHOD_WITH,
    ) {
    }
}
