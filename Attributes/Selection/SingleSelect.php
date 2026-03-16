<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection;

readonly class SingleSelect
{
    public function __construct(
        public string $displayProperty,
        public string $idProperty,
        public string $overlayTitle,
    ) {
    }

    /**
     * @return array<string,array<string,string>>
     */
    public function toArray(): array
    {
        return [
            'single_select' => [
                'display_property' => $this->displayProperty,
                'id_property' => $this->idProperty,
                'overlay_title' => $this->overlayTitle,
            ],
        ];
    }
}
