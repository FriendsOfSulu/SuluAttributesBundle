<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\BlockType;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsBlockType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;

#[AsBlockType(name: 'image', title: ['de' => 'Bild', 'en' => 'Image'])]
class ImageBlockType
{
    #[Property(type: FieldType::SINGLE_MEDIA_SELECTION, title: ['de' => 'Bild'], mandatory: true)]
    public array $image;
}
