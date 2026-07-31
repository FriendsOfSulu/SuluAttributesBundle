<?php

declare(strict_types=1);

namespace FriendsOfSulu\Bundle\SuluAttributesBundle\Form;

use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadataLoaderInterface;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\SectionMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\Validation\FieldMetadataValidatorInterface;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Component\VarExporter\VarExporter;

/**
 * Loads form metadata from PHP classes annotated with
 * {@see \FriendsOfSulu\Bundle\SuluAttributesBundle\Form\Attribute\AsForm}.
 *
 * This is the attribute-based counterpart of Sulu's
 * {@see \Sulu\Bundle\AdminBundle\Metadata\FormMetadata\XmlFormMetadataLoader}:
 * it participates in the same `sulu_admin.form_metadata_loader` pipeline and
 * writes into the very same form cache directory (`%sulu.cache_dir%/forms`),
 * so an attribute-defined form is indistinguishable from an XML-defined one to
 * the rest of Sulu.
 */
class AttributeFormMetadataLoader implements FormMetadataLoaderInterface, CacheWarmerInterface
{
    /**
     * @param iterable<object> $forms objects marked with #[AsForm]
     */
    public function __construct(
        private readonly iterable $forms,
        private readonly AttributeFormMetadataFactory $factory,
        private readonly FieldMetadataValidatorInterface $fieldMetadataValidator,
        private readonly string $cacheDir,
        private readonly bool $debug,
    ) {
    }

    public function getMetadata(string $key, string $locale, array $metadataOptions = []): ?FormMetadata
    {
        $path = $this->getCachePath($key);

        $formMetadata = @include $path;
        if ($formMetadata instanceof FormMetadata) {
            return $formMetadata;
        }

        if (!$this->debug) {
            return null;
        }

        $this->warmUp($this->cacheDir);

        $formMetadata = @include $path;

        return $formMetadata instanceof FormMetadata ? $formMetadata : null;
    }

    /**
     * @return array<string>
     */
    public function warmUp($cacheDir, ?string $buildDir = null): array
    {
        /** @var array<string, FormMetadata> $formsMetadataCollection */
        $formsMetadataCollection = [];

        foreach ($this->forms as $form) {
            $formMetadata = $this->factory->create($form);
            $formKey = $formMetadata->getKey();

            if (!\array_key_exists($formKey, $formsMetadataCollection)) {
                $formsMetadataCollection[$formKey] = $formMetadata;
            } else {
                $formsMetadataCollection[$formKey] = $formsMetadataCollection[$formKey]->merge($formMetadata);
            }
        }

        foreach ($formsMetadataCollection as $key => $formMetadata) {
            $this->validateItems($formMetadata->getItems(), $key);

            $path = $this->getCachePath($key);
            $this->writeCache($path, '<?php return ' . VarExporter::export($formMetadata) . ';');
        }

        return [];
    }

    public function isOptional(): bool
    {
        return false;
    }

    /**
     * @param array<mixed> $items
     */
    private function validateItems(array $items, string $formKey): void
    {
        foreach ($items as $item) {
            if ($item instanceof SectionMetadata) {
                $this->validateItems($item->getItems(), $formKey);
            }

            if ($item instanceof FieldMetadata) {
                foreach ($item->getTypes() as $type) {
                    $this->validateItems($type->getItems(), $formKey);
                }

                $this->fieldMetadataValidator->validate($item, $formKey);
            }
        }
    }

    private function getCachePath(string $key): string
    {
        return \sprintf('%s%s%s.php', $this->cacheDir, \DIRECTORY_SEPARATOR, $key);
    }

    private function writeCache(string $path, string $content): void
    {
        $dir = \dirname($path);
        if (!\is_dir($dir)) {
            \mkdir($dir, 0777, true);
        }

        $tmpFile = $path . '.' . \uniqid('', true);
        \file_put_contents($tmpFile, $content);
        \rename($tmpFile, $path);

        if (\function_exists('opcache_invalidate')) {
            opcache_invalidate($path, true);
        }
    }
}
