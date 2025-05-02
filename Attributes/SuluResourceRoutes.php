<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SuluResourceRoutes
{
    public function __construct(
        public readonly string $resourceKey,
        public readonly array $routes = [],
    ) {
    }
}
