<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;

#[AsForm]
class CustomTypeForm
{
    #[Property(type: FieldType::TextLine)]
    public string $builtin;

    #[Property(type: 'my_custom_content_type')]
    public string $custom;
}
