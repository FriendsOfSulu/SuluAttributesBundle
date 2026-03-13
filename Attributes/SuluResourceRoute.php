<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class SuluResourceRoute
{
    /** @param 'detail'|'list' $type */
    public function __construct(
        public readonly string $type,
        public readonly string $resourceKey,
    ) {
    }
}
