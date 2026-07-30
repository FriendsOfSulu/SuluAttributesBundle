<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute;

/**
 * A Sulu form `<tag>` (e.g. `sulu.rlp.part`, `sulu.search.field`), usable both
 * at form level ({@see AsForm}) and property level ({@see Property}).
 */
final class Tag
{
    /**
     * @param array<string, string> $attributes arbitrary extra attributes carried by the tag
     */
    public function __construct(
        public string $name,
        public ?int $priority = null,
        public array $attributes = [],
    ) {
    }
}
