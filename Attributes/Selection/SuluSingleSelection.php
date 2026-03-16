<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection;

#[\Attribute(\Attribute::TARGET_CLASS)]
readonly class SuluSingleSelection
{
    /**
     * @param array<AutoComplete|ListOverlay|SingleSelect> $types
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

    /**
     * @return array<string, mixed>
     */
    public function getTypes(): array
    {
        $types = [];
        foreach ($this->types as $type) {
            $types = [...$types, ...$type->toArray()];
        }

        return $types;
    }
}
