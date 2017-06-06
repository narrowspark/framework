<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Configuration;

use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use LaravelDoctrine\Fluent\Builders\Builder;
use LaravelDoctrine\Fluent\Extensions\ExtensibleClassMetadataFactory;
use LaravelDoctrine\Fluent\FluentDriver;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Support\AbstractManager;

class MetaDataManager extends AbstractManager implements ProvidesDefaultOptionsContract
{
    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            'default' => 'annotations',
            'drivers' => [
                'fluent'  => [
                    'mappings' => [],
                ],
                'annotations' => [
                    'simple' => false,
                    'paths'  => [],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'doctrine', $this->getConfigName()];
    }

    /**
     * Create an instance of the annotation meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\ORM\Mapping\Driver\AnnotationDriver
     */
    protected function createAnnotationsDriver(array $config): array
    {
        $configuration = new Configuration();

        return [
            'driver' => $configuration->newDefaultAnnotationDriver(
                $config['paths'],
                $config['simple']
            ),
            'meta_factory' => ClassMetadataFactory::class,
        ];
    }

    /**
     * Create an instance of the xml meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\ORM\Mapping\Driver\XmlDriver
     */
    protected function createXmlDriver(array $config): array
    {
        return [
            'driver' => new XmlDriver(
                $config['paths'],
                $config['extension'] ?? XmlDriver::DEFAULT_FILE_EXTENSION
            ),
            'meta_factory' => ClassMetadataFactory::class,
        ];
    }

    /**
     * Create an instance of the yaml meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\ORM\Mapping\Driver\YamlDriver
     */
    protected function createYamlDriver(array $config): array
    {
        return [
            'driver' => new YamlDriver(
                $config['paths'],
                $config['extension'] ?? YamlDriver::DEFAULT_FILE_EXTENSION
            ),
            'meta_factory' => ClassMetadataFactory::class,
        ];
    }

    /**
     * Create an instance of the simplified yaml meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver
     */
    protected function createSimplifiedYamlDriver(array $config): array
    {
        return [
            'driver' => new SimplifiedYamlDriver(
                $config['paths'],
                $config['extension'] ?? SimplifiedYamlDriver::DEFAULT_FILE_EXTENSION
            ),
            'meta_factory' => ClassMetadataFactory::class,
        ];
    }

    /**
     * Create an instance of the simplified xml meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver
     */
    protected function createSimplifiedXmlDriver(array $config): array
    {
        return [
            'driver' => new SimplifiedXmlDriver(
                $config['paths'],
                $config['extension'] ?? SimplifiedXmlDriver::DEFAULT_FILE_EXTENSION
            ),
            'meta_factory' => ClassMetadataFactory::class,
        ];
    }

    /**
     * Create an instance of the static php meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver
     */
    protected function createStaticPhpDriver(array $config): array
    {
        return [
            'driver'       => new StaticPHPDriver($config['paths']),
            'meta_factory' => ClassMetadataFactory::class,
        ];
    }

    /**
     * Create an instance of the php meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\PHPDriver
     */
    protected function createPhpDriver(array $config): array
    {
        return [
            'driver'       => new PHPDriver($config['paths']),
            'meta_factory' => ClassMetadataFactory::class,
        ];
    }

    /**
     * Create an instance of the fluent meta driver.
     *
     * @param array $config
     *
     * @return \LaravelDoctrine\Fluent\FluentDriver
     */
    protected function createFluentDriver(array $config): array
    {
        $driver = new FluentDriver($config['mappings']);

        $driver->setFluentFactory(function (ClassMetadataInfo $meta) {
            return new Builder(new ClassMetadataBuilder($meta));
        });

        return [
            'driver'       => $driver,
            'meta_factory' => ExtensibleClassMetadataFactory::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'metadata';
    }
}
