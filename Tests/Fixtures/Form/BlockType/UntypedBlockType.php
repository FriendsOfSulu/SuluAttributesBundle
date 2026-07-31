<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\BlockType;

/**
 * A class that is referenced as a block type but is missing the
 * #[AsBlockType] attribute — used to assert the factory rejects it.
 */
class UntypedBlockType
{
    public string $foo;
}
