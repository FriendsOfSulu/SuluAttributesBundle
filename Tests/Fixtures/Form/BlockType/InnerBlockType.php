<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\BlockType;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsBlockType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;

#[AsBlockType(name: 'inner', title: ['de' => 'Innen'])]
class InnerBlockType
{
    #[Property(type: FieldType::TEXT_LINE, title: ['de' => 'Beschriftung'])]
    public string $caption;
}
