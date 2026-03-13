<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class OtherMetadata
{
    public function __construct(
        public string $otherClassName,
        public string $entityAlias,
    ) {}
}

