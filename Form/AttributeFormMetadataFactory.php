<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsBlockType;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsSection;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Block;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Param;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Property;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Section;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\Tag;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\ItemMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\OptionMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\SchemaMetadataProvider;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\SectionMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\TagMetadata;

/**
 * Turns a PHP class annotated with {@see AsForm} into the exact same
 * {@see FormMetadata} object that Sulu's XML loader produces from a
 * `config/forms/*.xml` file.
 *
 * It mirrors
 * {@see \Sulu\Bundle\AdminBundle\Metadata\FormMetadata\Parser\PropertiesXmlParser}
 * and {@see \Sulu\Bundle\AdminBundle\Metadata\FormMetadata\Loader\FormXmlLoader},
 * reading PHP attributes instead of XML nodes.
 */
class AttributeFormMetadataFactory
{
    public function __construct(
        private readonly SchemaMetadataProvider $schemaMetadataProvider,
    ) {
    }

    /**
     * @param class-string|object $form
     */
    public function create(string|object $form): FormMetadata
    {
        $reflection = new \ReflectionClass($form);

        $formAttribute = $this->attribute($reflection, AsForm::class)
            ?? throw new \InvalidArgumentException(\sprintf(
                'Class "%s" is not a Sulu form. Add the #[%s] attribute.',
                $reflection->getName(),
                AsForm::class,
            ));

        $formMetadata = new FormMetadata();
        $formMetadata->addResource($reflection->getFileName() ?: $reflection->getName());
        $formMetadata->setKey($formAttribute->key ?? $this->nameFromClass($reflection->getShortName()));
        $formMetadata->setTitles($formAttribute->title);
        $formMetadata->setTags($this->buildTags($formAttribute->tags));

        $items = $this->buildItems($reflection);
        foreach ($items as $item) {
            $formMetadata->addItem($item);
        }

        // Schema (required properties, block/type schemas, ...) is derived
        // exactly like the XML loader does.
        $formMetadata->setSchema($this->schemaMetadataProvider->getMetadata($items));

        return $formMetadata;
    }

    /**
     * @param \ReflectionClass<object> $reflection
     *
     * @return list<ItemMetadata>
     */
    private function buildItems(\ReflectionClass $reflection): array
    {
        $items = [];

        foreach ($reflection->getProperties() as $property) {
            if ($section = $this->attribute($property, Section::class)) {
                $items[] = $this->buildSection($property->getName(), $section);
            } elseif ($block = $this->attribute($property, Block::class)) {
                $items[] = $this->buildBlock($property->getName(), $block);
            } elseif ($field = $this->attribute($property, Property::class)) {
                $items[] = $this->buildField($property->getName(), $field);
            }
        }

        return $items;
    }

    private function buildField(string $propertyName, Property $attribute): FieldMetadata
    {
        $field = new FieldMetadata($attribute->name ?? $propertyName);
        $field->setType($attribute->type instanceof FieldType ? $attribute->type->value : $attribute->type);
        $field->setRequired($attribute->mandatory);
        $field->setMultilingual($attribute->multilingual);
        $field->setMinOccurs($attribute->minOccurs);
        $field->setMaxOccurs($attribute->maxOccurs);
        $field->setSpaceAfter($attribute->spaceAfter);
        $field->setOnInvalid($attribute->onInvalid);
        $field->setTags($this->buildTags($attribute->tags));

        $this->applyCommon(
            $field,
            $attribute->title,
            $attribute->infoText,
            $attribute->colSpan,
            $attribute->disabledCondition,
            $attribute->visibleCondition,
        );

        foreach ($this->normalizeParams($attribute->params) as $param) {
            $field->addOption($this->buildOption($param));
        }

        return $field;
    }

    private function buildBlock(string $propertyName, Block $attribute): FieldMetadata
    {
        $block = new FieldMetadata($attribute->name ?? $propertyName);
        $block->setType(FieldType::Block->value);
        $block->setRequired($attribute->mandatory);
        $block->setMultilingual($attribute->multilingual);
        $block->setMinOccurs($attribute->minOccurs);
        $block->setMaxOccurs($attribute->maxOccurs);
        $block->setSpaceAfter($attribute->spaceAfter);
        $block->setTags($this->buildTags($attribute->tags));

        $this->applyCommon(
            $block,
            $attribute->title,
            $attribute->infoText,
            $attribute->colSpan,
            $attribute->disabledCondition,
            $attribute->visibleCondition,
        );

        $firstTypeName = null;
        foreach ($attribute->types as $typeClass) {
            $type = $this->buildBlockType($typeClass);
            $firstTypeName ??= $type->getKey();
            $block->addType($type);
        }

        $block->setDefaultType($attribute->defaultType ?? $firstTypeName);

        return $block;
    }

    /**
     * @param class-string $typeClass
     */
    private function buildBlockType(string $typeClass): FormMetadata
    {
        $reflection = new \ReflectionClass($typeClass);

        $typeAttribute = $this->attribute($reflection, AsBlockType::class)
            ?? throw new \InvalidArgumentException(\sprintf(
                'Block type class "%s" is missing the #[%s] attribute.',
                $reflection->getName(),
                AsBlockType::class,
            ));

        $type = new FormMetadata();
        $type->setKey($typeAttribute->name ?? $this->nameFromClass($reflection->getShortName()));
        $type->setTitles($typeAttribute->title);

        foreach ($this->buildItems($reflection) as $item) {
            $type->addItem($item);
        }

        return $type;
    }

    private function buildSection(string $propertyName, Section $attribute): SectionMetadata
    {
        $reflection = new \ReflectionClass($attribute->properties);
        $sectionDefaults = $this->attribute($reflection, AsSection::class);

        $section = new SectionMetadata($attribute->name ?? $propertyName);
        $section->setLabels($attribute->title ?: ($sectionDefaults->title ?? []));
        $section->setDescriptions($attribute->infoText ?: ($sectionDefaults->infoText ?? []));

        if (null !== $attribute->colSpan) {
            $section->setColSpan($attribute->colSpan);
        }
        $section->setDisabledCondition($attribute->disabledCondition);
        $section->setVisibleCondition($attribute->visibleCondition);

        foreach ($this->buildItems($reflection) as $item) {
            $section->addItem($item);
        }

        return $section;
    }

    /**
     * @param array<string, string> $title
     * @param array<string, string> $infoText
     */
    private function applyCommon(
        ItemMetadata $item,
        array $title,
        array $infoText,
        ?int $colSpan,
        ?string $disabledCondition,
        ?string $visibleCondition,
    ): void {
        $item->setLabels($title);
        $item->setDescriptions($infoText);
        if (null !== $colSpan) {
            $item->setColSpan($colSpan);
        }
        $item->setDisabledCondition($disabledCondition);
        $item->setVisibleCondition($visibleCondition);
    }

    /**
     * Mirrors PropertiesXmlParser::mapProperty(): the outer option of a
     * collection carries the type but no scalar value, while the nested value
     * options carry name/value/meta but no type.
     */
    private function buildOption(Param $param, bool $nested = false): OptionMetadata
    {
        $option = new OptionMetadata();
        $option->setName($param->name);
        $option->setTitles($param->title);
        $option->setInfoTexts($param->infoText);
        $option->setPlaceholders($param->placeholder);

        if (!$nested) {
            $option->setType($param->type->value);
        }

        if (\is_array($param->value)) {
            foreach ($param->value as $child) {
                $option->addValueOption($this->buildOption($child, nested: true));
            }
        } else {
            // Sulu's XML parser casts "true"/"false" to bool and numeric strings
            // to int; PHP attributes already carry the native scalar type.
            $option->setValue($param->value);
        }

        return $option;
    }

    /**
     * Accepts the scalar shorthand (`['headline' => true]`) as well as a list
     * of {@see Param} objects, and any mix of the two.
     *
     * @param array<array-key, scalar|Param> $params
     *
     * @return list<Param>
     */
    private function normalizeParams(array $params): array
    {
        $normalized = [];
        foreach ($params as $key => $value) {
            $normalized[] = $value instanceof Param ? $value : new Param((string) $key, $value);
        }

        return $normalized;
    }

    /**
     * @param list<Tag> $tags
     *
     * @return list<TagMetadata>
     */
    private function buildTags(array $tags): array
    {
        return \array_map(static function(Tag $tag): TagMetadata {
            $metadata = new TagMetadata();
            $metadata->setName($tag->name);
            $metadata->setPriority($tag->priority);
            $metadata->setAttributes($tag->attributes);

            return $metadata;
        }, $tags);
    }

    /**
     * @template T of object
     *
     * @param \ReflectionClass<object>|\ReflectionProperty $reflection
     * @param class-string<T> $attributeClass
     *
     * @return T|null
     */
    private function attribute(\ReflectionClass|\ReflectionProperty $reflection, string $attributeClass): ?object
    {
        $attributes = $reflection->getAttributes($attributeClass);

        return isset($attributes[0]) ? $attributes[0]->newInstance() : null;
    }

    /**
     * `MemoryCardDetailsForm` -> `memory_card_details`, `TextEcoType` -> `text_eco`.
     */
    private function nameFromClass(string $shortName): string
    {
        foreach (['Form', 'Type', 'Block', 'Section'] as $suffix) {
            if ($shortName !== $suffix && \str_ends_with($shortName, $suffix)) {
                $shortName = \substr($shortName, 0, -\strlen($suffix));
                break;
            }
        }

        return \strtolower((string) \preg_replace('/(?<!^)[A-Z]/', '_$0', $shortName));
    }
}
