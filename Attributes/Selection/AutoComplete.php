<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection;

readonly class AutoComplete
{
    /**
     * @param array<string> $searchProperties
     */
    public function __construct(
        public string $displayProperty,
        public ?array $searchProperties = null,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'auto_complete' => [
                'display_property' => $this->displayProperty,
                'search_properties' => $this->searchProperties ?? [$this->displayProperty],
            ],
        ];
    }
}
