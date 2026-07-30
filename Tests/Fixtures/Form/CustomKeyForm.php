<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;

#[AsForm(key: 'custom_key')]
class CustomKeyForm
{
    #[Property(type: FieldType::TEXT_LINE, title: ['de' => 'Name'])]
    public string $name;
}
