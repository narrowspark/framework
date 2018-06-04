<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Support\Tests\Fixture\TestManager;

/**
 * @internal
 */
final class AbstractManagerTest extends MockeryTestCase
{
    public function testDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'test',
                    'drivers' => [
                        'test'        => ['driver' => 'test'],
                        'config'      => ['driver' => 'config'],
                        'value'       => ['driver' => 'foo'],
                        'testmanager' => ['driver' => 'testmanager'],
                    ],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));

        $this->assertTrue($manager->getDriver('test'));

        $this->assertEquals(['name' => 'config', 'driver' => 'config'], $manager->getDriver('config'));

        $this->assertEquals(['name' => 'value', 'driver' => 'foo'], $manager->getDriver('value'));
        $this->assertTrue($manager->hasDriver('value'));
        $this->assertEquals([
            'test'   => true,
            'config' => ['name' => 'config', 'driver' => 'config'],
            'value'  => ['name' => 'value', 'driver' => 'foo'],
        ], $manager->getDrivers());

        $this->assertInstanceOf('stdClass', $manager->getDriver('testmanager'));
    }

    public function testCustomeDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'test',
                    'drivers' => [
                        'custom' => [''],
                    ],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));
        $manager->extend('custom', function () {
            return 'custom';
        });

        $this->assertSame('custom', $manager->getDriver('custom'));
    }

    public function testDriverToThrowException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Driver [dont] is not supported.');

        $manager = new TestManager(new ArrayContainer([
            'config' => [
                'viserio' => [
                    'test' => [
                        'default' => 'test',
                        'drivers' => [],
                    ],
                ],
            ],
        ]));
        $manager->getDriver('dont');
    }

    public function testCall(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'test',
                    'drivers' => [],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));
        $manager->extend('call', function () {
            return new ArrayContainer();
        });
        $manager->setDefaultDriver('call');

        $driver = $manager->getDriver('call');

        $this->assertInstanceOf(ArrayContainer::class, $driver);

        $manager->set('test', 'test');

        $this->assertSame('test', $manager->get('test'));
    }

    public function testCustomDriverClosureBoundObjectIsCacheManager(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => __CLASS__,
                    'drivers' => [
                        __CLASS__ => [''],
                    ],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));

        $driver = function () {
            return $this;
        };
        $manager->extend(__CLASS__, $driver);

        $this->assertEquals($manager, $manager->getDriver(__CLASS__));
        $this->assertTrue($manager->hasDriver(__CLASS__));
    }

    public function testGetDriverConfig(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'pdo',
                    'drivers' => [
                        'pdo' => [
                            'servers' => 'localhost',
                        ],
                    ],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));

        $this->assertInternalType('array', $manager->getDriverConfig('pdo'));
    }

    public function testDefaultDriver(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'test' => [
                    'default' => 'example',
                    'drivers' => [],
                ],
            ]);

        $manager = new TestManager(new ArrayContainer([RepositoryContract::class => $config]));

        $this->assertSame('example', $manager->getDefaultDriver());

        $manager->setDefaultDriver('new');

        $this->assertSame('new', $manager->getDefaultDriver());
    }
}
