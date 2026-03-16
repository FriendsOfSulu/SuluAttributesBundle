<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class SuluMultiSelection
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        public string $name,
        public array $config
    ) {
    }
}
