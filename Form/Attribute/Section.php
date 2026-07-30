<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute;

/**
 * Describes a `<section>` — a visual grouping of properties.
 *
 * The referenced class holds the section's properties (via {@see Property} /
 * {@see Block}) and may carry an {@see AsSection} attribute for its title.
 * Sections may be nested (a section class can itself have a {@see Section}
 * property).
 *
 * ```php
 * #[Section(SeoData::class, title: ['de' => 'SEO'])]
 * public object $seo;
 * ```
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class Section
{
    /**
     * @param class-string $properties class holding the section's properties
     * @param array<string, string> $title title per locale (overrides the referenced class' title)
     * @param array<string, string> $infoText info text per locale
     * @param string|null $name section name; defaults to the PHP property name
     * @param int|null $colSpan grid column span (1-12, default 12)
     * @param string|null $disabledCondition jexl expression; section is disabled while it evaluates to true
     * @param string|null $visibleCondition jexl expression; section is hidden while it evaluates to false
     */
    public function __construct(
        public string $properties,
        public array $title = [],
        public array $infoText = [],
        public ?string $name = null,
        public ?int $colSpan = null,
        public ?string $disabledCondition = null,
        public ?string $visibleCondition = null,
    ) {
    }
}
