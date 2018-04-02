<?php
declare(strict_types=1);
namespace Viserio\Component\Discovery\Test\Configurator;

use Composer\Composer;
use Composer\IO\NullIO;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Foundation\Kernel;
use Viserio\Component\Discovery\Configurator\ServiceProviderConfigurator;
use Viserio\Component\Discovery\Package;

class ServiceProviderConfiguratorTest extends MockeryTestCase
{
    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\IO\NullIo
     */
    private $nullIo;

    /**
     * @var \Viserio\Component\Discovery\Configurator\ServiceProviderConfigurator
     */
    private $configurator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->composer = new Composer();
        $this->nullIo   = new NullIO();

        $config = [
            'config-dir' => __DIR__,
        ];

        $this->configurator = new ServiceProviderConfigurator($this->composer, $this->nullIo, $config);
    }

    public function testConfigureWithGlobalProvider(): void
    {
        $package = new Package(
            'test',
            __DIR__,
            [
                'package_version'  => '1',
                Package::CONFIGURE => [
                    'providers' => [
                        'global' => [
                            self::class,
                        ],
                    ],
                ],
            ]
        );

        $this->configurator->configure($package);

        $filePath = __DIR__ . '/serviceproviders.php';

        $array = include $filePath;

        self::assertSame(self::class, $array[0]);

        \unlink($filePath);
    }

    public function testConfigureWithGlobalAndLocalProvider(): void
    {
        $package = new Package(
            'test',
            __DIR__,
            [
                'package_version'  => '1',
                Package::CONFIGURE => [
                    'providers' => [
                        'global' => [
                            self::class,
                        ],
                        'local' => [
                            self::class,
                        ],
                    ],
                ],
            ]
        );

        $this->configurator->configure($package);

        $kernel = $this->mock(Kernel::class);
        $kernel->shouldReceive('isLocal')
            ->andReturn(false);
        $kernel->shouldReceive('isRunningUnitTests')
            ->andReturn(true);

        $filePath = __DIR__ . '/serviceproviders.php';

        $array = include $filePath;

        self::assertSame(self::class, $array[0]);
        self::assertSame(self::class, $array[1]);

        \unlink($filePath);
    }

    public function testSkipMarkedFiles(): void
    {
        $package = new Package(
            'test',
            __DIR__,
            [
                'package_version'  => '1',
                Package::CONFIGURE => [
                    'providers' => [
                        'global' => [
                            self::class,
                        ],
                    ],
                ],
            ]
        );

        $this->configurator->configure($package);

        $filePath = __DIR__ . '/serviceproviders.php';

        $array = include $filePath;

        self::assertSame(self::class, $array[0]);

        $package = new Package(
        'test',
        __DIR__,
            [
                'package_version'  => '1',
                Package::CONFIGURE => [
                    'providers' => [
                        'global' => [
                            self::class,
                            Package::class,
                        ],
                    ],
                ],
            ]
        );

        $this->configurator->configure($package);

        self::assertFalse(isset($array[1]));

        \unlink($filePath);
    }

    public function testUpdateAExistedFileWithGlobalAndLocalProvider(): void
    {
        $package = new Package(
            'test',
            __DIR__,
            [
                'package_version'  => '1',
                Package::CONFIGURE => [
                    'providers' => [
                        'global' => [
                            self::class,
                        ],
                        'local' => [
                            self::class,
                        ],
                    ],
                ],
            ]
        );

        $this->configurator->configure($package);

        $kernel = $this->mock(Kernel::class);
        $kernel->shouldReceive('isLocal')
            ->andReturn(false);
        $kernel->shouldReceive('isRunningUnitTests')
            ->andReturn(true);

        $filePath = __DIR__ . '/serviceproviders.php';

        $array = include $filePath;

        self::assertSame(self::class, $array[0]);
        self::assertSame(self::class, $array[1]);

        $package = new Package(
            'test2',
            __DIR__,
            [
                'package_version'  => '1',
                Package::CONFIGURE => [
                    'providers' => [
                        'global' => [
                            Package::class,
                        ],
                        'local' => [
                            Package::class,
                        ],
                    ],
                ],
            ]
        );

        $this->configurator->configure($package);

        $kernel = $this->mock(Kernel::class);
        $kernel->shouldReceive('isLocal')
            ->andReturn(false);
        $kernel->shouldReceive('isRunningUnitTests')
            ->andReturn(true);

        $filePath = __DIR__ . '/serviceproviders.php';

        $array = include $filePath;

        self::assertSame(self::class, $array[0]);
        self::assertSame(Package::class, $array[1]);
        self::assertSame(self::class, $array[2]);
        self::assertSame(Package::class, $array[3]);

        \unlink($filePath);
    }
}
