<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute;

use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\OptionMetadata;

/**
 * The three parameter kinds Sulu supports for `<param>` entries.
 */
enum ParamType: string
{
    case String = OptionMetadata::TYPE_STRING;
    case Expression = OptionMetadata::TYPE_EXPRESSION;
    case Collection = OptionMetadata::TYPE_COLLECTION;
}
