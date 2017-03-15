<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\ORM\Tests\Configuration;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use PHPUnit\Framework\TestCase;
use LaravelDoctrine\Fluent\FluentDriver;
use Doctrine\Common\Persistence\Mapping\Driver\PHPDriver;
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
                ]
            ]
        );

        self::assertInstanceOf(AnnotationDriver::class, $manager->getDriver());
        self::assertInstanceOf(AnnotationDriver::class, $manager->getDriver('annotations'));
    }

    public function testGetDriverWithXml()
    {
        $manager = new MetaDataManager(
            [
                'viserio' => [
                    'doctrine' => [
                        'metadata' => [
                            'default' => 'xml',
                            'drivers' => [
                                'xml' => [
                                    'paths' => [
                                        __DIR__
                                    ]
                                ]
                            ],
                        ],
                    ],
                ]
            ]
        );

        self::assertInstanceOf(XmlDriver::class, $manager->getDriver('xml'));
    }

    public function testGetDriverWithYaml()
    {
        $manager = new MetaDataManager(
            [
                'viserio' => [
                    'doctrine' => [
                        'metadata' => [
                            'default' => 'yaml',
                            'drivers' => [
                                'yaml' => [
                                    'paths' => [
                                        __DIR__
                                    ]
                                ]
                            ],
                        ],
                    ],
                ]
            ]
        );

        self::assertInstanceOf(YamlDriver::class, $manager->getDriver('yaml'));
    }

    public function testGetDriverWithSimplifiedYaml()
    {
        $manager = new MetaDataManager(
            [
                'viserio' => [
                    'doctrine' => [
                        'metadata' => [
                            'default' => 'simplified_yaml',
                            'drivers' => [
                                'simplified_yaml' => [
                                    'paths' => [
                                        __DIR__
                                    ]
                                ]
                            ],
                        ],
                    ],
                ]
            ]
        );

        self::assertInstanceOf(SimplifiedYamlDriver::class, $manager->getDriver('simplified_yaml'));
    }

    public function testGetDriverWithSimplifiedXml()
    {
        $manager = new MetaDataManager(
            [
                'viserio' => [
                    'doctrine' => [
                        'metadata' => [
                            'default' => 'simplified_xml',
                            'drivers' => [
                                'simplified_xml' => [
                                    'paths' => [
                                        __DIR__
                                    ]
                                ]
                            ],
                        ],
                    ],
                ]
            ]
        );

        self::assertInstanceOf(SimplifiedXmlDriver::class, $manager->getDriver('simplified_xml'));
    }

    public function testGetDriverWithStaticPHP()
    {
        $manager = new MetaDataManager(
            [
                'viserio' => [
                    'doctrine' => [
                        'metadata' => [
                            'default' => 'static_php',
                            'drivers' => [
                                'static_php' => [
                                    'paths' => [
                                        __DIR__
                                    ]
                                ]
                            ],
                        ],
                    ],
                ]
            ]
        );

        self::assertInstanceOf(StaticPHPDriver::class, $manager->getDriver('static_php'));
    }

    public function testGetDriverWithPHP()
    {
        $manager = new MetaDataManager(
            [
                'viserio' => [
                    'doctrine' => [
                        'metadata' => [
                            'default' => 'php',
                            'drivers' => [
                                'php' => [
                                    'paths' => [
                                        __DIR__
                                    ]
                                ]
                            ],
                        ],
                    ],
                ]
            ]
        );

        self::assertInstanceOf(PHPDriver::class, $manager->getDriver('php'));
    }

    public function testGetDriverWithFluent()
    {
        $manager = new MetaDataManager(
            [
                'viserio' => [
                    'doctrine' => [
                        'metadata' => [
                            'default' => 'fluent',
                            'drivers' => [
                                'fluent' => [
                                    'paths' => [
                                        __DIR__
                                    ]
                                ]
                            ],
                        ],
                    ],
                ]
            ]
        );

        self::assertInstanceOf(FluentDriver::class, $manager->getDriver('fluent'));
    }
}
