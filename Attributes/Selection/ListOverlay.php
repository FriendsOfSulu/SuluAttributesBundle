<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection;

readonly class ListOverlay
{
    /**
     * @param array<int,mixed> $detailOptions
     * @param array<int,mixed> $displayProperties
     */
    public function __construct(
        public string $adapter,
        public string $listKey,
        public array $displayProperties,
        public string $icon,
        public string $emptyText,
        public string $overlayTitle,
        public array $detailOptions = [],
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'list_overlay' => [
                'adapter' => $this->adapter,
                'detail_options' => $this->detailOptions,
                'list_key' => $this->listKey,
                'display_properties' => $this->displayProperties,
                'icon' => $this->icon,
                'empty_text' => $this->emptyText,
                'overlay_title' => $this->overlayTitle,
            ],
        ];
    }
}
