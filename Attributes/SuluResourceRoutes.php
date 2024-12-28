<?php declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class SuluResourceRoutes
{
    /**
     * @param array<string, string> $options
     */
    public function __construct(
        public readonly string $resourceKey,
        public readonly array $routes = [],
    ) {
    }
}
