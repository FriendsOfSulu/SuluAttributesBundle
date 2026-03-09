<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration;

use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class CountPropertyMetadata
{
    public function __construct(
        public readonly string $visibility=FieldDescriptorInterface::VISIBILITY_YES,
        public readonly string $searchability=FieldDescriptorInterface::SEARCHABILITY_YES,
        public readonly bool $sortable = true,
        public readonly string $width = FieldDescriptorInterface::WIDTH_AUTO,
        public readonly ?string $title=null,
        public readonly bool $distinct = false,
    ) {
    }
}