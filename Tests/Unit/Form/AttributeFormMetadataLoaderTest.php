<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Unit\Form;

use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\AttributeFormMetadataFactory;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Form\AttributeFormMetadataLoader;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\ExampleForm;
use FriendsOfSulu\Bundle\SuluAttributesBundle\Tests\Fixtures\Form\SimpleForm;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\SchemaMetadataProvider;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\Validation\FieldMetadataValidatorInterface;
use Sulu\Bundle\AdminBundle\Metadata\SchemaMetadata\SchemaMetadata;

class AttributeFormMetadataLoaderTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var ObjectProphecy<FieldMetadataValidatorInterface>
     */
    private ObjectProphecy $fieldMetadataValidator;

    private AttributeFormMetadataFactory $factory;

    private string $cacheDir;

    protected function setUp(): void
    {
        $schemaMetadataProvider = $this->prophesize(SchemaMetadataProvider::class);
        $schemaMetadataProvider->getMetadata(Argument::type('array'))->willReturn(new SchemaMetadata());
        $this->factory = new AttributeFormMetadataFactory($schemaMetadataProvider->reveal());

        $this->fieldMetadataValidator = $this->prophesize(FieldMetadataValidatorInterface::class);

        $this->cacheDir = \sys_get_temp_dir() . \DIRECTORY_SEPARATOR . \uniqid('sulu_attributes_forms_', true);
    }

    protected function tearDown(): void
    {
        if (!\is_dir($this->cacheDir)) {
            return;
        }

        foreach ((array) \glob($this->cacheDir . \DIRECTORY_SEPARATOR . '*') as $file) {
            \unlink((string) $file);
        }
        \rmdir($this->cacheDir);
    }

    /**
     * @param iterable<object> $forms
     */
    private function createLoader(iterable $forms, bool $debug = true): AttributeFormMetadataLoader
    {
        return new AttributeFormMetadataLoader(
            $forms,
            $this->factory,
            $this->fieldMetadataValidator->reveal(),
            $this->cacheDir,
            $debug,
        );
    }

    public function testWarmUpWritesACacheFilePerForm(): void
    {
        $loader = $this->createLoader([new SimpleForm(), new ExampleForm()]);

        $this->assertSame([], $loader->warmUp($this->cacheDir));

        $this->assertFileExists($this->cacheDir . \DIRECTORY_SEPARATOR . 'simple.php');
        $this->assertFileExists($this->cacheDir . \DIRECTORY_SEPARATOR . 'example.php');

        $simple = include $this->cacheDir . \DIRECTORY_SEPARATOR . 'simple.php';
        $this->assertInstanceOf(FormMetadata::class, $simple);
        $this->assertSame('simple', $simple->getKey());
    }

    public function testGetMetadataReturnsFormFromCache(): void
    {
        $loader = $this->createLoader([new SimpleForm()]);
        $loader->warmUp($this->cacheDir);

        $metadata = $loader->getMetadata('simple', 'de');

        $this->assertInstanceOf(FormMetadata::class, $metadata);
        $this->assertSame('simple', $metadata->getKey());
    }

    public function testGetMetadataWarmsUpOnDemandInDebugMode(): void
    {
        $loader = $this->createLoader([new SimpleForm()], debug: true);

        // No explicit warmUp(): the loader must build the cache on the fly.
        $metadata = $loader->getMetadata('simple', 'de');

        $this->assertInstanceOf(FormMetadata::class, $metadata);
        $this->assertSame('simple', $metadata->getKey());
    }

    public function testGetMetadataReturnsNullForUnknownKey(): void
    {
        $loader = $this->createLoader([new SimpleForm()], debug: true);

        $this->assertNull($loader->getMetadata('does_not_exist', 'de'));
    }

    public function testGetMetadataReturnsNullWhenNotInDebugAndCacheIsCold(): void
    {
        $loader = $this->createLoader([new SimpleForm()], debug: false);

        $this->assertNull($loader->getMetadata('simple', 'de'));
    }

    public function testWarmUpValidatesEveryField(): void
    {
        $this->fieldMetadataValidator
            ->validate(Argument::type(FieldMetadata::class), 'simple')
            ->shouldBeCalledTimes(1);

        $loader = $this->createLoader([new SimpleForm()]);
        $loader->warmUp($this->cacheDir);
    }

    public function testIsOptionalReturnsFalse(): void
    {
        $this->assertFalse($this->createLoader([])->isOptional());
    }
}
