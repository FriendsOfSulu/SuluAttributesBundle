<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class SuluResourceRoutes
{
    /**
     * @param array<string, string> $routes
     */
    public function __construct(
        public readonly string $resourceKey,
        public readonly array $routes = [],
    ) {
    }
}
