<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form;

/**
 * A plain class without the #[AsForm] attribute — used to assert the factory
 * rejects classes that are not forms.
 */
class PlainClass
{
    public string $foo;
}
