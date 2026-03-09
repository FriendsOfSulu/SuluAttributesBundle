<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration;

use Sulu\Component\Rest\ListBuilder\FieldDescriptorInterface;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
readonly class SinglePropertyMetadata
{
    public function __construct(
        public ?string $name = null,
        public string $visibility = FieldDescriptorInterface::VISIBILITY_YES,
        public string $searchability = FieldDescriptorInterface::SEARCHABILITY_YES,
        public bool $sortable = true,
        public string $width = FieldDescriptorInterface::WIDTH_AUTO,
        public ?string $title = null,
        public ?string $type = null,
        public ?array $transformerTypeParameters = null,
        public string $filterType = '',
        public ?array $filterTypeParameters = null,
    ) {
    }
}
