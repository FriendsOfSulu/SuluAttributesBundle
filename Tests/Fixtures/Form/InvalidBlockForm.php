<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Block;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\BlockType\UntypedBlockType;

#[AsForm]
class InvalidBlockForm
{
    #[Block(types: [UntypedBlockType::class])]
    public array $blocks;
}
