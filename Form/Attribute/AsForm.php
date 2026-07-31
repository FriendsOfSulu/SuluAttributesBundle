<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute;

/**
 * Marks a PHP class as a Sulu form definition.
 *
 * A class carrying this attribute is the PHP equivalent of a
 * `config/forms/*.xml` file: the
 * {@see \FriendsOfSulu\Bundle\SuluAttributesBundle\Form\AttributeFormMetadataFactory}
 * turns it into the very same
 * {@see \Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata} object
 * that the XML loader produces, and it is written into the same form cache.
 *
 * The form key defaults to the snake_case of the class name (with a trailing
 * "Form" stripped), e.g. `MemoryCardDetailsForm` -> `memory_card_details`.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsForm
{
    /**
     * @param string|null $key form key; defaults to the snake_cased class name
     * @param array<string, string> $title optional form title per locale, e.g. ['de' => 'Titel']
     * @param list<Tag> $tags form-level tags
     */
    public function __construct(
        public ?string $key = null,
        public array $title = [],
        public array $tags = [],
    ) {
    }
}
