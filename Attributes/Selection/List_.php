<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\Selection;

readonly class List_
{
    public function __construct(
        public string $adapter,
        public string $listKey,
    ) {
    }

    /**
     * @return array<string,array<string,string>>
     */
    public function toArray(): array
    {
        return ['list' => ['adapter' => $this->adapter, 'listKey' => $this->listKey]];
    }
}
