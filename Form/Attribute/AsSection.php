<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute;

/**
 * Marks a class as a form section holder (referenced by {@see Section}).
 *
 * Optional: a section can also be referenced without this attribute — it only
 * provides a default title and lets the class be reused across forms.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsSection
{
    /**
     * @param array<string, string> $title title per locale
     * @param array<string, string> $infoText info text per locale
     */
    public function __construct(
        public array $title = [],
        public array $infoText = [],
    ) {
    }
}
