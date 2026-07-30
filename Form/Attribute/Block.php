<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute;

/**
 * Describes a `block` property with a set of selectable types, the PHP
 * equivalent of `<block>` with its nested `<types>`.
 *
 * Each entry in {@see self::$types} is a class-string of a class carrying the
 * {@see AsBlockType} attribute. Block types may themselves contain {@see Block}
 * properties — nested blocks work out of the box.
 *
 * ```php
 * #[Block(
 *     types: [TextBlock::class, ImageBlock::class],
 *     title: ['de' => 'Inhalt'],
 * )]
 * public array $content;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Block
{
    /**
     * @param list<class-string> $types block type classes (each marked with #[AsBlockType])
     * @param string|null $defaultType resolved name of the default type; defaults to the first type
     * @param array<string, string> $title title per locale
     * @param array<string, string> $infoText info text per locale
     * @param bool $mandatory whether at least one block is required
     * @param bool $multilingual whether the block content is translatable
     * @param list<Tag> $tags block-level tags
     * @param string|null $name block name; defaults to the PHP property name
     * @param int|null $colSpan grid column span (1-12, default 12)
     * @param int|null $spaceAfter empty grid columns rendered after the block
     * @param int|null $minOccurs minimum number of block instances
     * @param int|null $maxOccurs maximum number of block instances
     * @param string|null $disabledCondition jexl expression; block is disabled while it evaluates to true
     * @param string|null $visibleCondition jexl expression; block is hidden while it evaluates to false
     */
    public function __construct(
        public array $types,
        public ?string $defaultType = null,
        public array $title = [],
        public array $infoText = [],
        public bool $mandatory = false,
        public bool $multilingual = true,
        public array $tags = [],
        public ?string $name = null,
        public ?int $colSpan = null,
        public ?int $spaceAfter = null,
        public ?int $minOccurs = null,
        public ?int $maxOccurs = null,
        public ?string $disabledCondition = null,
        public ?string $visibleCondition = null,
    ) {
    }
}
