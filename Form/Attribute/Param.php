<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute;

/**
 * A single `<param>` entry of a property.
 *
 * The common case never needs this class — a scalar shorthand is enough:
 *
 * ```php
 * params: ['headline' => true, 'types' => 'image']
 * ```
 *
 * Use {@see Param} for expressions and collections:
 *
 * ```php
 * params: [
 *     new Param('provider', 'pages'),
 *     new Param('sortBy', 'published', ParamType::Expression),
 *     new Param('present_as', type: ParamType::Collection, value: [
 *         new Param('two', 'Two columns'),
 *     ]),
 * ]
 * ```
 */
final class Param
{
    /**
     * @param scalar|list<Param> $value scalar value, or a list of {@see Param} for collections
     * @param array<string, string> $title optional per-locale title (collection entries)
     * @param array<string, string> $infoText optional per-locale info text
     * @param array<string, string> $placeholder optional per-locale placeholder
     */
    public function __construct(
        public string $name,
        public string|int|float|bool|array $value = '',
        public ParamType $type = ParamType::String,
        public array $title = [],
        public array $infoText = [],
        public array $placeholder = [],
    ) {
    }
}
