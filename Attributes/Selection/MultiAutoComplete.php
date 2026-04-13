<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection;

readonly class MultiAutoComplete
{
    /**
     * @param array<string> $searchProperties
     */
    public function __construct(
        public string $displayProperty,
        public bool $allowAdd = false,
        public string $idProperty = 'id',
        public ?array $searchProperties = null,
        public ?string $filterParameter = null,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'auto_complete' => [
                'allow_add' => $this->allowAdd,
                'id_property' => $this->idProperty,
                'display_property' => $this->displayProperty,
                'filter_parameter' => $this->filterParameter,
                'search_properties' => $this->searchProperties ?? [$this->displayProperty],
            ],
        ];
    }
}
