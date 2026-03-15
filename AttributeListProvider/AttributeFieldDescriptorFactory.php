<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\AttributeListProvider;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration\ConcatPropertyMetadata;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration\CountPropertyMetadata;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration\JoinMetadata;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration\OtherMetadata;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration\SinglePropertyMetadata;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineConcatenationFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineCountFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineFieldDescriptor;
use Sulu\Component\Rest\ListBuilder\Doctrine\FieldDescriptor\DoctrineJoinDescriptor;
use Sulu\Component\Rest\ListBuilder\Metadata\ConcatenationPropertyMetadata;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;

final readonly class AttributeFieldDescriptorFactory implements FieldDescriptorFactoryInterface, CacheWarmerInterface
{
    public function __construct(
        private FieldDescriptorFactoryInterface $inner,
    ) {
    }

    public function getFieldDescriptors(string $listKey): ?array
    {
        if (!\class_exists($listKey)) {
            return $this->inner->getFieldDescriptors($listKey);
        }

        $reflection = new \ReflectionClass($listKey);

        $joins = $this->readJoinInfo($reflection);

        $fields = [];
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(SinglePropertyMetadata::class);
            $concatAttributes = $property->getAttributes(ConcatPropertyMetadata::class);
            $countAttributes = $property->getAttributes(CountPropertyMetadata::class);
            $otherAttributes = $property->getAttributes(OtherMetadata::class);
            if ([] === $attributes && [] === $concatAttributes && [] === $countAttributes && [] === $otherAttributes) {
                continue;
            }

            foreach (\array_merge($attributes, $concatAttributes, $countAttributes, $otherAttributes) as $attribute) {
                $attributeInstance = $attribute->newInstance();

                if ($attributeInstance instanceof SinglePropertyMetadata) {
                    $fields[$attributeInstance->name ?? $property->getName()] = $this->getSinglePropertyMetadata($property, $attributeInstance);
                }
                if ($attributeInstance instanceof ConcatPropertyMetadata) {
                    $fields[$attributeInstance->name ?? $property->getName()] = $this->getConcatenationPropertyMetadata($property, $attributeInstance, $joins);
                }
                if ($attributeInstance instanceof CountPropertyMetadata) {
                    $fields[$attributeInstance->name ?? $property->getName()] = $this->getCountFieldDescriptor($property, $attributeInstance);
                }
                if ($attributeInstance instanceof OtherMetadata) {
                    $fields = [...$fields, ...$this->getOtherFieldDescriptors($attributeInstance)];
                }
            }
        }

        if ([] === $fields) {
            throw new \InvalidArgumentException('No class-based mapping found for entity: '. $listKey);
        }

        return $fields;
    }

    private function getSinglePropertyMetadata(
        \ReflectionProperty $property,
        SinglePropertyMetadata $attribute
    ): DoctrineFieldDescriptor {
        $propertyName = $attribute->name ?? $property->getName();
        $type = $attribute->type ?? $this->guessFieldTypeFromTypeInfo($property->getType(), $propertyName);

        $fieldDescriptor = new DoctrineFieldDescriptor(
            $attribute->fieldName ?? $property->getName(),
            $propertyName,
            $attribute->entityAlias ?? $property->getDeclaringClass()->getName(),
            $attribute->title ?? ('sulu_admin.' . $property->getName()),
            [],
            $attribute->visibility,
            $attribute->searchability,
            $type,
            $attribute->sortable,
            $attribute->width,
        );

        $subMetadata = new \Sulu\Component\Rest\ListBuilder\Metadata\SinglePropertyMetadata($propertyName);
        if (null !== $attribute->transformerTypeParameters) {
            $subMetadata->setTransformerTypeParameters($attribute->transformerTypeParameters);
        }
        $subMetadata->setFilterType($attribute->filterType);

        $fieldDescriptor->setMetadata($subMetadata);

        return $fieldDescriptor;
    }

    /**
     * @param array<DoctrineJoinDescriptor> $joins
     */
    private function getConcatenationPropertyMetadata(
        \ReflectionProperty $property,
        ConcatPropertyMetadata $attribute,
        array $joins,
    ): DoctrineConcatenationFieldDescriptor {
        $fieldsToConcat = array_map(
            fn (string $fieldExpression) => self::parseFieldDescriptorFromExpression($fieldExpression, $property, $joins),
            $attribute->fields
        );

        $fieldDescriptor = new DoctrineConcatenationFieldDescriptor(
            $fieldsToConcat,
            $property->getName(),
            $attribute->title ?? ('sulu_admin.' . $property->getName()),
            $attribute->glue,
            $attribute->visibility,
            $attribute->searchability,
            'string',
            $attribute->sortable,
            $attribute->width,
        );
        $fieldDescriptor->setMetadata(new ConcatenationPropertyMetadata($property->getName()));

        return $fieldDescriptor;
    }

    private function getCountFieldDescriptor(
        \ReflectionProperty $property,
        CountPropertyMetadata $attribute
    ): DoctrineCountFieldDescriptor {
        $fieldDescriptor = new DoctrineCountFieldDescriptor(
            $property->getName(),
            $property->getName(),
            $property->getDeclaringClass()->getName(),
            $attribute->title ?? ('sulu_admin.' . $property->getName()),
            [],
            $attribute->visibility,
            $attribute->searchability,
            'number',
            $attribute->sortable,
            $attribute->distinct,
            $attribute->width,
        );

        $fieldDescriptor->setMetadata(new \Sulu\Component\Rest\ListBuilder\Metadata\CountPropertyMetadata($property->getName()));

        return $fieldDescriptor;
    }

    /**
 * @return array<string, DoctrineFieldDescriptor>
 */
    private function getOtherFieldDescriptors(OtherMetadata $attribute): array {
        $fields = [];
        $reflection = new \ReflectionClass($attribute->otherClassName);

        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(SinglePropertyMetadata::class);
            $attributeInstance = ($attributes[0] ?? null)?->newInstance();

            if ($attributeInstance instanceof SinglePropertyMetadata) {
                $attributeInstance->entityAlias = $attribute->entityAlias;
                $fields[$attributeInstance->name ?? $property->getName()] = $this->getSinglePropertyMetadata($property, $attributeInstance);
            }
        }

        return $fields;
    }

    /**
     * @param \ReflectionClass<object> $reflection
     *
     * @return array<DoctrineJoinDescriptor>
     */
    private function readJoinInfo(\ReflectionClass $reflection): array
    {
        $joins = [];
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(JoinMetadata::class);

            foreach ($attributes as $i => $join) {
                /** @var JoinMetadata $attributeInstance */
                $attributeInstance = $join->newInstance();

                $joinDescriptor = new DoctrineJoinDescriptor(
                    join('.', self::parseJoinExpression($attributeInstance->joinExpression, $property)),
                    $attributeInstance->joinAlias,
                    $attributeInstance->joinCondition,
                    $attributeInstance->joinMethod,
                    $attributeInstance->joinConditionMethod,
                );
                $joins[$attributeInstance->joinAlias] = $joinDescriptor;
            }
        }

        return $joins;
    }

    /**
    * @param array<DoctrineJoinDescriptor> $joins
    */
    private static function parseFieldDescriptorFromExpression(
        string $expression,
        \ReflectionProperty $property,
        array $joins,
    ): DoctrineFieldDescriptor {
        [$entityAlias, $fieldName] = self::parseJoinExpression($expression, $property);

        return new DoctrineFieldDescriptor(
            $fieldName,
            $property->getName(),
            $entityAlias,
            '',
            $joins,
        );
    }

    /**
    * @return array<string>
    */
    private static function parseJoinExpression(string $expression, \ReflectionProperty $property): array {
        return match (substr_count($expression, '.')) {
            // No points means current entity and the expression is the field name
            0 => [$property->getDeclaringClass()->getName(), $expression],
            // One dot means, foreign entity + expression (user.username)
            1 => explode('.', $expression),
            // More dots are not supported
            default => throw new \InvalidArgumentException(sprintf(
                'Expression "%s" is too complex. Please only specify at most one relation like: user.username',
                $expression
            )),
        };
    }

    private function guessFieldTypeFromTypeInfo(?\ReflectionType $type, string $propertyName): string
    {
        return match (ltrim((string) $type, '?')) {
            'string' => 'string',
            'int' | 'float' => 'number',
            \DateTimeInterface::class, \DateTimeImmutable::class => 'datetime',
            default => throw new \InvalidArgumentException('Please specify the type for "' . $propertyName . '". Current type: ' . $type),
        };
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        return [];
    }

    public function isOptional(): bool
    {
        return false;
    }
}
