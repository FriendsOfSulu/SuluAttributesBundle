<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute;

/**
 * Marks a class as a block type (a `<type>` inside a `<block>`).
 *
 * The properties of the annotated class ({@see Property} / {@see Block})
 * become the properties of the block type. The type name defaults to the
 * snake_case of the class name (with a trailing "Type" or "Block" stripped),
 * e.g. `TextEcoType` -> `text_eco`, `ImageBlock` -> `image`.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsBlockType
{
    /**
     * @param string|null $name type name; defaults to the snake_cased class name
     * @param array<string, string> $title title per locale
     */
    public function __construct(
        public ?string $name = null,
        public array $title = [],
    ) {
    }
}
