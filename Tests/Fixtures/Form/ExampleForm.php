<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Block;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Param;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\ParamType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Section;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Tag;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\FieldType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\BlockType\ImageBlockType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\BlockType\TextBlockType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\Section\SeoSection;

/**
 * A form fixture that exercises every supported building block: a property with
 * meta/params/tags, an expression- and a collection-param, a block with two
 * types, a block with an explicit default type and a section reference.
 */
#[AsForm(
    title: ['de' => 'Beispiel'],
    tags: [new Tag('sulu.search.field', priority: 100, attributes: ['index' => 'default'])],
)]
class ExampleForm
{
    #[Property(
        type: FieldType::TEXT_LINE,
        title: ['de' => 'Titel', 'en' => 'Title'],
        infoText: ['de' => 'Der Titel'],
        mandatory: true,
        multilingual: false,
        params: ['headline' => true],
        tags: [new Tag('sulu.rlp.part', priority: 1)],
        colSpan: 6,
        spaceAfter: 2,
    )]
    public string $title;

    #[Property(
        type: FieldType::SMART_CONTENT,
        params: [
            new Param('provider', 'pages'),
            new Param('sortBy', 'published', ParamType::Expression),
            new Param('present_as', type: ParamType::Collection, value: [
                new Param('two', 'Two columns'),
            ]),
        ],
    )]
    public array $teaser;

    #[Block(types: [TextBlockType::class, ImageBlockType::class], title: ['de' => 'Inhalt'], mandatory: true)]
    public array $content;

    #[Block(types: [TextBlockType::class, ImageBlockType::class], defaultType: 'image')]
    public array $gallery;

    #[Section(SeoSection::class)]
    public object $seo;
}
