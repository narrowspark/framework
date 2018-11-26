<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Configuration;

use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use LaravelDoctrine\Fluent\Extensions\ExtensibleClassMetadataFactory;
use LaravelDoctrine\Fluent\FluentDriver;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\ORM\Configuration\MetaDataManager;

class MetaDataManagerTest extends TestCase
{
    public function testGetDriverWithAnnotations(): void
    {
        $manager = new MetaDataManager(
            [
                'viserio' => [
                    'doctrine' => [
                        'metadata' => [
                            'drivers' => [
                            ],
                        ],
                    ],
                ],
            ]
        );

        $driver = $manager->getDriver();
        self::assertInstanceOf(AnnotationDriver::class, $driver['driver']);
        self::assertSame(ClassMetadataFactory::class, $driver['meta_factory']);

        $driver = $manager->getDriver('annotations');

        self::assertInstanceOf(AnnotationDriver::class, $driver['driver']);
        self::assertSame(ClassMetadataFactory::class, $driver['meta_factory']);
    }

    /**
     * @dataProvider metaDriverProvider
     *
     * @param mixed $driverClass
     * @param mixed $driverName
     * @param mixed $driverInfos
     * @param array $config
     */
    public function testMetaDataDriver(array $config, $driverInfos, $driverName): void
    {
        $manager = new MetaDataManager(
            [
                'viserio' => [
                    'doctrine' => [
                        'metadata' => $config,
                    ],
                ],
            ]
        );

        $driver = $manager->getDriver($driverName);

        self::assertInstanceOf($driverInfos['driver'], $driver['driver']);
        self::assertSame($driverInfos['meta_factory'], $driver['meta_factory']);
    }

    public function metaDriverProvider()
    {
        return [
            [['default' => 'xml', 'drivers' => ['xml' => ['paths' => [__DIR__]]]], ['driver' => XmlDriver::class, 'meta_factory' => ClassMetadataFactory::class], 'xml'],
            [['default' => 'yaml', 'drivers' => ['yaml' => ['paths' => [__DIR__]]]], ['driver' => YamlDriver::class, 'meta_factory' => ClassMetadataFactory::class], 'yaml'],
            [['default' => 'simplified_yaml', 'drivers' => ['simplified_yaml' => ['paths' => [__DIR__]]]], ['driver' => SimplifiedYamlDriver::class, 'meta_factory' => ClassMetadataFactory::class], 'simplified_yaml'],
            [['default' => 'simplified_xml', 'drivers' => ['simplified_xml' => ['paths' => [__DIR__]]]], ['driver' => SimplifiedXmlDriver::class, 'meta_factory' => ClassMetadataFactory::class], 'simplified_xml'],
            [['default' => 'static_php', 'drivers' => ['static_php' => ['paths' => [__DIR__]]]], ['driver' => StaticPHPDriver::class, 'meta_factory' => ClassMetadataFactory::class], 'static_php'],
            [['default' => 'php', 'drivers' => ['php' => ['paths' => [__DIR__]]]], ['driver' => PHPDriver::class, 'meta_factory' => ClassMetadataFactory::class], 'php'],
            [['default' => 'fluent', 'drivers' => ['fluent' => ['paths' => [__DIR__]]]], ['driver' => FluentDriver::class, 'meta_factory' => ExtensibleClassMetadataFactory::class], 'fluent'],
        ];
    }
}
