<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\BlockType;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsBlockType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Block;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;

#[AsBlockType(name: 'outer', title: ['de' => 'Außen'])]
class OuterBlockType
{
    #[Property(type: FieldType::TextLine, title: ['de' => 'Überschrift'])]
    public string $heading;

    #[Block(types: [InnerBlockType::class], title: ['de' => 'Elemente'])]
    public array $items;
}
