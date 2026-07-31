<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;

/**
 * Sulu's built-in field types as a string-backed enum, for autocompletion and
 * typo-safety.
 *
 * The enum is not exhaustive by design: {@see Property::$type} accepts a plain
 * string as well, so ANY registered content type works — custom ones from your
 * project or third-party bundles included:
 *
 * ```php
 * #[Property(type: FieldType::TextLine)]      // built-in, via the enum
 * #[Property(type: 'my_custom_content_type')] // custom, via a plain string
 * ```
 */
enum FieldType: string
{
    // Text
    case TextLine = 'text_line';
    case TextArea = 'text_area';
    case TextEditor = 'text_editor';
    case Email = 'email';
    case Url = 'url';
    case Phone = 'phone';
    case Password = 'password';
    case Color = 'color';

    // Numbers / dates
    case Number = 'number';
    case Percent = 'percent';
    case Date = 'date';
    case Time = 'time';
    case DateTime = 'datetime';

    // Choices
    case Checkbox = 'checkbox';
    case Toggler = 'toggler';
    case SingleSelect = 'single_select';
    case Select = 'select';

    // Selections / relations
    case TagSelection = 'tag_selection';
    case CategorySelection = 'category_selection';
    case SingleCategorySelection = 'single_category_selection';
    case MediaSelection = 'media_selection';
    case SingleMediaSelection = 'single_media_selection';
    case PageSelection = 'page_selection';
    case SinglePageSelection = 'single_page_selection';
    case SnippetSelection = 'snippet_selection';
    case TeaserSelection = 'teaser_selection';
    case ContactSelection = 'contact_account_selection';
    case SmartContent = 'smart_content';

    // Structure
    case ResourceLocator = 'resource_locator';
    case Block = 'block';
}
