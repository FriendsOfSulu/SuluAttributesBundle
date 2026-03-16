<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class SuluSingleSelection
{
    /**
     * @param array<string, mixed> $types
     * @param ?array<string, mixed> $views
     */
    public function __construct(
        public string $name,
        public string $defaultType,
        public string $resourceKey,
        public array $types,
        public ?array $views = null,
    ) {
    }
}
