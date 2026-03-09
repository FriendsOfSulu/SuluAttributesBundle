<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\AttributeListProvider;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration\ConcatPropertyMetadata;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration\CountPropertyMetadata;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration\JoinMetadata;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Attributes\ListConfiguration\SinglePropertyMetadata;
use Sulu\Bundle\SecurityBundle\Entity\User;
use Sulu\Bundle\SnippetBundle\Document\SnippetDocument;
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
            if ([] === $attributes && [] === $concatAttributes && [] === $countAttributes) {
                continue;
            }

            foreach (\array_merge($attributes, $concatAttributes, $countAttributes) as $attribute) {
                $attributeInstance = $attribute->newInstance();

                if ($attributeInstance instanceof SinglePropertyMetadata) {
                    $fields[$property->getName()] = $this->getSinglePropertyMetadata($property, $attributeInstance);
                }
                if ($attributeInstance instanceof ConcatPropertyMetadata) {
                    $fields[$property->getName()] = $this->getConcatenationPropertyMetadata($property, $attributeInstance);
                }
                if ($attributeInstance instanceof CountPropertyMetadata) {
                    $fields[$property->getName()] = $this->getCountFieldDescriptor($property, $attributeInstance);
                }
            }
        }

        return $fields;
    }

    private function getSinglePropertyMetadata(
        \ReflectionProperty $property,
        SinglePropertyMetadata $attribute
    ): DoctrineFieldDescriptor {
        $propertyName = $property->getName();
        $type = $attribute->type ?? $this->guessFieldTypeFromTypeInfo($property->getType(), $propertyName);

        $fieldDescriptor = new DoctrineFieldDescriptor(
            $propertyName,
            $attribute->name ?? $propertyName,
            $property->getDeclaringClass()->getName(),
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

    private function getConcatenationPropertyMetadata(
        \ReflectionProperty $property,
        ConcatPropertyMetadata $attribute
    ): DoctrineConcatenationFieldDescriptor {
        $fieldDescriptor = new DoctrineConcatenationFieldDescriptor(
            [
                new DoctrineFieldDescriptor(
                    'username',
                    $property->getName(),
                    User::class,
                    '',
                    [
                        User::class => new DoctrineJoinDescriptor(User::class, SnippetDocument::class . '.' . $property->getName()),
                    ],
                ),
            ],
            $property->getName(),
            $attribute->title ?? ('sulu_admin.' . $property->getName()),
            $attribute->glue,
            $attribute->visibility,
            $attribute->searchability,
            'string',
            $attribute->sortable,
            $attribute->width
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
     * @param \ReflectionClass<object> $reflection
     *
     * @return array<DoctrineJoinDescriptor>
     */
    private function readJoinInfo(\ReflectionClass $reflection): array
    {
        $joins = [];
        foreach ($reflection->getProperties() as $property) {
            $attributes = $property->getAttributes(JoinMetadata::class);
            if ([] === $attributes) {
                continue;
            }
            /** @var JoinMetadata $attributeInstance */
            $attributeInstance = $attributes[0]->newInstance();

            $property = new DoctrineJoinDescriptor(
                $attributeInstance->entityClass ?? ((string) $property->getType()),
                $attributeInstance->name,
                $attributeInstance->joinCondition,
                $attributeInstance->joinMethod,
                $attributeInstance->joinConditionMethod,
            );
            $joins[] = $property;
        }

        return $joins;
    }

    private function guessFieldTypeFromTypeInfo(?\ReflectionType $type, string $propertyName): string
    {
        return match ((string) $type) {
            'string' => 'text',
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
