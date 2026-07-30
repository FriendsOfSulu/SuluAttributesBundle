<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\BlockType;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsBlockType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;

#[AsBlockType(title: ['de' => 'Text', 'en' => 'Text'])]
class TextBlockType
{
    #[Property(type: FieldType::TEXT_EDITOR, title: ['de' => 'Text'], mandatory: true)]
    public string $text;
}
