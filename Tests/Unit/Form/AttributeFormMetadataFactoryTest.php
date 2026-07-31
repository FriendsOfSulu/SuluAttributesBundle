<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Unit\Form;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\AttributeFormMetadataFactory;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\CustomKeyForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\CustomTypeForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\ExampleForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\InvalidBlockForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\NestedBlockForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\PlainClass;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\SimpleForm;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\OptionMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\SchemaMetadataProvider;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\SectionMetadata;
use Sulu\Bundle\AdminBundle\Metadata\SchemaMetadata\SchemaMetadata;

class AttributeFormMetadataFactoryTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<SchemaMetadataProvider>
     */
    private ObjectProphecy $schemaMetadataProvider;

    private SchemaMetadata $schema;

    private AttributeFormMetadataFactory $factory;

    protected function setUp(): void
    {
        $this->schema = new SchemaMetadata();
        $this->schemaMetadataProvider = $this->prophesize(SchemaMetadataProvider::class);
        $this->schemaMetadataProvider->getMetadata(Argument::type('array'))->willReturn($this->schema);

        $this->factory = new AttributeFormMetadataFactory($this->schemaMetadataProvider->reveal());
    }

    public function testCreateThrowsWhenClassIsNotAForm(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/is not a Sulu form/');

        $this->factory->create(PlainClass::class);
    }

    public function testKeyIsDerivedFromClassName(): void
    {
        $this->assertSame('simple', $this->factory->create(new SimpleForm())->getKey());
    }

    public function testKeyCanBeSetExplicitly(): void
    {
        $this->assertSame('custom_key', $this->factory->create(new CustomKeyForm())->getKey());
    }

    public function testCreateAcceptsAClassString(): void
    {
        $this->assertSame('simple', $this->factory->create(SimpleForm::class)->getKey());
    }

    public function testFormTitlesAndResourceAreMapped(): void
    {
        $form = $this->factory->create(new ExampleForm());

        $this->assertSame(['de' => 'Beispiel'], $form->getTitles());
        $this->assertNotEmpty($form->getResources());
        $this->assertStringEndsWith('ExampleForm.php', $form->getResources()[0]);
    }

    public function testFormTagsAreMapped(): void
    {
        $tags = $this->factory->create(new ExampleForm())->getTags();

        $this->assertCount(1, $tags);
        $this->assertSame('sulu.search.field', $tags[0]->getName());
        $this->assertSame(100, $tags[0]->getPriority());
        $this->assertSame(['index' => 'default'], $tags[0]->getAttributes());
    }

    public function testSchemaIsProvidedBySchemaMetadataProvider(): void
    {
        $form = $this->factory->create(new ExampleForm());

        $this->assertSame($this->schema, $form->getSchema());
    }

    public function testFieldTypeEnumIsResolvedToItsStringValue(): void
    {
        $builtin = $this->factory->create(new CustomTypeForm())->getItems()['builtin'];
        \assert($builtin instanceof FieldMetadata);

        $this->assertSame('text_line', $builtin->getType());
    }

    public function testCustomStringTypeIsPassedThrough(): void
    {
        $custom = $this->factory->create(new CustomTypeForm())->getItems()['custom'];
        \assert($custom instanceof FieldMetadata);

        $this->assertSame('my_custom_content_type', $custom->getType());
    }

    public function testPropertyIsMapped(): void
    {
        $title = $this->factory->create(new ExampleForm())->getItems()['title'];

        $this->assertInstanceOf(FieldMetadata::class, $title);
        $this->assertSame('title', $title->getName());
        $this->assertSame('text_line', $title->getType());
        $this->assertTrue($title->isRequired());
        $this->assertFalse($title->isMultilingual());
        $this->assertSame(6, $title->getColSpan());
        $this->assertSame(2, $title->getSpaceAfter());
        $this->assertSame(['de' => 'Titel', 'en' => 'Title'], $title->getLabels());
        $this->assertSame(['de' => 'Der Titel'], $title->getDescriptions());
    }

    public function testPropertyTagsAreMapped(): void
    {
        $title = $this->factory->create(new ExampleForm())->getItems()['title'];
        \assert($title instanceof FieldMetadata);

        $tags = $title->getTags();
        $this->assertCount(1, $tags);
        $this->assertSame('sulu.rlp.part', $tags[0]->getName());
        $this->assertSame(1, $tags[0]->getPriority());
    }

    public function testScalarParamShorthandBecomesStringOption(): void
    {
        $title = $this->factory->create(new ExampleForm())->getItems()['title'];
        \assert($title instanceof FieldMetadata);

        $option = $title->findOption('headline');
        $this->assertInstanceOf(OptionMetadata::class, $option);
        $this->assertSame(OptionMetadata::TYPE_STRING, $option->getType());
        $this->assertTrue($option->getValue());
    }

    public function testExpressionParamIsMapped(): void
    {
        $teaser = $this->factory->create(new ExampleForm())->getItems()['teaser'];
        \assert($teaser instanceof FieldMetadata);

        $option = $teaser->findOption('sortBy');
        $this->assertInstanceOf(OptionMetadata::class, $option);
        $this->assertSame(OptionMetadata::TYPE_EXPRESSION, $option->getType());
        $this->assertSame('published', $option->getValue());
    }

    public function testCollectionParamIsMappedWithNestedValueOptions(): void
    {
        $teaser = $this->factory->create(new ExampleForm())->getItems()['teaser'];
        \assert($teaser instanceof FieldMetadata);

        $option = $teaser->findOption('present_as');
        $this->assertInstanceOf(OptionMetadata::class, $option);
        $this->assertSame(OptionMetadata::TYPE_COLLECTION, $option->getType());

        $valueOptions = $option->getValue();
        $this->assertIsArray($valueOptions);
        $this->assertCount(1, $valueOptions);

        $nested = $valueOptions[0];
        $this->assertInstanceOf(OptionMetadata::class, $nested);
        $this->assertSame('two', $nested->getName());
        $this->assertSame('Two columns', $nested->getValue());
        // Nested value options carry no own type, mirroring Sulu's XML parser.
        $this->assertNull($nested->getType());
    }

    public function testBlockIsMapped(): void
    {
        $content = $this->factory->create(new ExampleForm())->getItems()['content'];

        $this->assertInstanceOf(FieldMetadata::class, $content);
        $this->assertSame('block', $content->getType());
        $this->assertTrue($content->isRequired());
        $this->assertSame(['de' => 'Inhalt'], $content->getLabels());
        $this->assertSame(['text_block', 'image'], \array_keys($content->getTypes()));
    }

    public function testBlockDefaultTypeDefaultsToFirstType(): void
    {
        $content = $this->factory->create(new ExampleForm())->getItems()['content'];
        \assert($content instanceof FieldMetadata);

        $this->assertSame('text_block', $content->getDefaultType());
    }

    public function testBlockDefaultTypeCanBeSetExplicitly(): void
    {
        $gallery = $this->factory->create(new ExampleForm())->getItems()['gallery'];
        \assert($gallery instanceof FieldMetadata);

        $this->assertSame('image', $gallery->getDefaultType());
    }

    public function testBlockTypePropertiesAreMapped(): void
    {
        $content = $this->factory->create(new ExampleForm())->getItems()['content'];
        \assert($content instanceof FieldMetadata);

        $textType = $content->getTypes()['text_block'];
        $this->assertInstanceOf(FormMetadata::class, $textType);
        $this->assertSame('text_block', $textType->getKey());
        $this->assertSame(['de' => 'Text', 'en' => 'Text'], $textType->getTitles());
        $this->assertArrayHasKey('text', $textType->getItems());
    }

    public function testBlockTypeNameCanBeSetExplicitly(): void
    {
        $content = $this->factory->create(new ExampleForm())->getItems()['content'];
        \assert($content instanceof FieldMetadata);

        $this->assertArrayHasKey('image', $content->getTypes());
        $this->assertSame('image', $content->getTypes()['image']->getKey());
    }

    public function testNestedBlocksAreSupported(): void
    {
        $blocks = $this->factory->create(new NestedBlockForm())->getItems()['blocks'];
        \assert($blocks instanceof FieldMetadata);

        $outer = $blocks->getTypes()['outer'];
        $inner = $outer->getItems()['items'];
        $this->assertInstanceOf(FieldMetadata::class, $inner);
        $this->assertSame('block', $inner->getType());
        $this->assertArrayHasKey('inner', $inner->getTypes());
    }

    public function testSectionIsMapped(): void
    {
        $seo = $this->factory->create(new ExampleForm())->getItems()['seo'];

        $this->assertInstanceOf(SectionMetadata::class, $seo);
        // Labels/descriptions fall back to the #[AsSection] defaults.
        $this->assertSame(['de' => 'SEO', 'en' => 'SEO'], $seo->getLabels());
        $this->assertSame(['de' => 'Suchmaschinen'], $seo->getDescriptions());
        $this->assertArrayHasKey('metaTitle', $seo->getItems());
    }

    public function testBlockTypeMissingAttributeThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/is missing the .* attribute/');

        $this->factory->create(new InvalidBlockForm());
    }
}
