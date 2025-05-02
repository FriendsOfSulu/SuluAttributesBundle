<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class SuluResourceRoute
{
    /** @var 'detail'|'list' */
    public function __construct(
        public readonly string $type,
        public readonly string $resourceKey,
    ) {
    }
}
