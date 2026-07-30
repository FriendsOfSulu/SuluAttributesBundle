<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\Section;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsSection;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;

#[AsSection(title: ['de' => 'SEO', 'en' => 'SEO'], infoText: ['de' => 'Suchmaschinen'])]
class SeoSection
{
    #[Property(type: FieldType::TEXT_LINE, title: ['de' => 'Meta Titel'])]
    public string $metaTitle;
}
