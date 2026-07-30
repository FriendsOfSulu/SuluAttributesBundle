<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;

/**
 * Describes a single form property, the PHP equivalent of a `<property>`:
 *
 * ```xml
 * <property name="title" type="text_line" mandatory="true">
 *     <meta>
 *         <title lang="de">Titel</title>
 *         <info_text lang="de">...</info_text>
 *     </meta>
 *     <params>
 *         <param name="headline" value="true"/>
 *     </params>
 * </property>
 * ```
 *
 * becomes
 *
 * ```php
 * #[Property(
 *     type: FieldType::TextLine,
 *     title: ['de' => 'Titel'],
 *     mandatory: true,
 *     params: ['headline' => true],
 * )]
 * public string $title;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Property
{
    /**
     * @param string|FieldType $type Sulu field type; a {@see FieldType} case for built-ins or any string for custom content types
     * @param array<string, string> $title title per locale, e.g. ['de' => 'Titel', 'en' => 'Title']
     * @param array<string, string> $infoText info text per locale (description shown below the field)
     * @param bool $mandatory whether the field is required
     * @param bool $multilingual whether the value is translatable
     * @param array<string, scalar>|list<Param>|array<string, scalar|Param> $params `<param>` entries (scalar shorthand or {@see Param} objects)
     * @param list<Tag> $tags property-level tags
     * @param string|null $name property name; defaults to the PHP property name
     * @param int|null $colSpan grid column span (1-12, default 12)
     * @param int|null $spaceAfter empty grid columns rendered after the field
     * @param int|null $minOccurs minimum number of values (repeatable fields)
     * @param int|null $maxOccurs maximum number of values (repeatable fields)
     * @param string|null $disabledCondition jexl expression; field is disabled while it evaluates to true
     * @param string|null $visibleCondition jexl expression; field is hidden while it evaluates to false
     * @param string|null $onInvalid behaviour on invalid value, e.g. "ignore"
     */
    public function __construct(
        public string|FieldType $type,
        public array $title = [],
        public array $infoText = [],
        public bool $mandatory = false,
        public bool $multilingual = true,
        public array $params = [],
        public array $tags = [],
        public ?string $name = null,
        public ?int $colSpan = null,
        public ?int $spaceAfter = null,
        public ?int $minOccurs = null,
        public ?int $maxOccurs = null,
        public ?string $disabledCondition = null,
        public ?string $visibleCondition = null,
        public ?string $onInvalid = null,
    ) {
    }
}
