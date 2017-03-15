<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Configuration;

use Doctrine\ORM\Configuration;
use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use LaravelDoctrine\Fluent\Builders\Builder;
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
    protected function createAnnotationsDriver(array $config): AnnotationDriver
    {
        $configuration = new Configuration();

        return $configuration->newDefaultAnnotationDriver(
            $config['paths'],
            $config['simple']
        );
    }

    /**
     * Create an instance of the xml meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\ORM\Mapping\Driver\XmlDriver
     */
    protected function createXmlDriver(array $config): XmlDriver
    {
        return new XmlDriver(
            $config['paths'],
            $config['extension'] ?? XmlDriver::DEFAULT_FILE_EXTENSION
        );
    }

    /**
     * Create an instance of the yaml meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\ORM\Mapping\Driver\YamlDriver
     */
    protected function createYamlDriver(array $config): YamlDriver
    {
        return new YamlDriver(
            $config['paths'],
            $config['extension'] ?? YamlDriver::DEFAULT_FILE_EXTENSION
        );
    }

    /**
     * Create an instance of the simplified yaml meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver
     */
    protected function createSimplifiedYamlDriver(array $config): SimplifiedYamlDriver
    {
        return new SimplifiedYamlDriver(
            $config['paths'],
            $config['extension'] ?? SimplifiedYamlDriver::DEFAULT_FILE_EXTENSION
        );
    }

    /**
     * Create an instance of the simplified xml meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver
     */
    protected function createSimplifiedXmlDriver(array $config): SimplifiedXmlDriver
    {
        return new SimplifiedXmlDriver(
            $config['paths'],
            $config['extension'] ?? SimplifiedXmlDriver::DEFAULT_FILE_EXTENSION
        );
    }

    /**
     * Create an instance of the static php meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver
     */
    protected function createStaticPhpDriver(array $config): StaticPHPDriver
    {
        return new StaticPHPDriver($config['paths']);
    }

    /**
     * Create an instance of the php meta driver.
     *
     * @param array $config
     *
     * @return \Doctrine\Common\Persistence\Mapping\Driver\PHPDriver
     */
    protected function createPhpDriver(array $config): PHPDriver
    {
        return new PHPDriver($config['paths']);
    }

    /**
     * Create an instance of the fluent meta driver.
     *
     * @param array $config
     *
     * @return \LaravelDoctrine\Fluent\FluentDriver
     */
    protected function createFluentDriver(array $config): FluentDriver
    {
        $driver = new FluentDriver($config['mappings']);

        $driver->setFluentFactory(function (ClassMetadataInfo $meta) {
            return new Builder(new ClassMetadataBuilder($meta));
        });

        return $driver;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigName(): string
    {
        return 'metadata';
    }
}
