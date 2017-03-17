<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Configuration;

use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use LaravelDoctrine\Fluent\FluentDriver;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Doctrine\ORM\Configuration\MetaDataManager;

class MetaDataManagerTest extends TestCase
{
    public function testGetDriverWithAnnotations()
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

        self::assertInstanceOf(AnnotationDriver::class, $manager->getDriver());
        self::assertInstanceOf(AnnotationDriver::class, $manager->getDriver('annotations'));
    }

    /**
     * @dataProvider metaDriverProvider
     *
     * @param mixed $driverClass
     * @param mixed $driverName
     */
    public function testMetaDataDriver(array $config, $driverClass, $driverName)
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

        self::assertInstanceOf($driverClass, $manager->getDriver($driverName));
    }

    public function metaDriverProvider()
    {
        return [
            [['default' => 'xml', 'drivers' => ['xml' => ['paths' => [__DIR__]]]], XmlDriver::class, 'xml'],
            [['default' => 'yaml', 'drivers' => ['yaml' => ['paths' => [__DIR__]]]], YamlDriver::class, 'yaml'],
            [['default' => 'simplified_yaml', 'drivers' => ['simplified_yaml' => ['paths' => [__DIR__]]]], SimplifiedYamlDriver::class, 'simplified_yaml'],
            [['default' => 'simplified_xml', 'drivers' => ['simplified_xml' => ['paths' => [__DIR__]]]], SimplifiedXmlDriver::class, 'simplified_xml'],
            [['default' => 'static_php', 'drivers' => ['static_php' => ['paths' => [__DIR__]]]], StaticPHPDriver::class, 'static_php'],
            [['default' => 'php', 'drivers' => ['php' => ['paths' => [__DIR__]]]], PHPDriver::class, 'php'],
            [['default' => 'fluent', 'drivers' => ['fluent' => ['paths' => [__DIR__]]]], FluentDriver::class, 'fluent'],
        ];
    }
}
