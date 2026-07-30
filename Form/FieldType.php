<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form;

/**
 * Convenience constants for Sulu's built-in field types.
 *
 * The property `type` is a free-form string — exactly as in XML — so ANY
 * registered content type works, including custom ones from your project or
 * third-party bundles. These constants exist purely for autocompletion and to
 * avoid typos for the most common types. Pass a plain string for anything not
 * listed here:
 *
 * ```php
 * #[Property(type: 'my_custom_content_type')]
 * ```
 */
final class FieldType
{
    // Text
    public const TEXT_LINE = 'text_line';
    public const TEXT_AREA = 'text_area';
    public const TEXT_EDITOR = 'text_editor';
    public const EMAIL = 'email';
    public const URL = 'url';
    public const PHONE = 'phone';
    public const PASSWORD = 'password';
    public const COLOR = 'color';

    // Numbers / dates
    public const NUMBER = 'number';
    public const PERCENT = 'percent';
    public const DATE = 'date';
    public const TIME = 'time';
    public const DATE_TIME = 'datetime';

    // Choices
    public const CHECKBOX = 'checkbox';
    public const TOGGLER = 'toggler';
    public const SINGLE_SELECT = 'single_select';
    public const SELECT = 'select';

    // Selections / relations
    public const TAG_SELECTION = 'tag_selection';
    public const CATEGORY_SELECTION = 'category_selection';
    public const SINGLE_CATEGORY_SELECTION = 'single_category_selection';
    public const MEDIA_SELECTION = 'media_selection';
    public const SINGLE_MEDIA_SELECTION = 'single_media_selection';
    public const PAGE_SELECTION = 'page_selection';
    public const SINGLE_PAGE_SELECTION = 'single_page_selection';
    public const SNIPPET_SELECTION = 'snippet_selection';
    public const TEASER_SELECTION = 'teaser_selection';
    public const CONTACT_SELECTION = 'contact_account_selection';
    public const SMART_CONTENT = 'smart_content';

    // Structure
    public const RESOURCE_LOCATOR = 'resource_locator';
    public const BLOCK = 'block';

    private function __construct()
    {
    }
}
