<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Support\Tests\Fixture\TestConnectionManager;

/**
 * @internal
 */
final class AbstractConnectionManagerTest extends MockeryTestCase
{
    public function testConnectionToThrowRuntimeException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Connection [fail] is not supported.');

        $manager = new TestConnectionManager(new ArrayContainer([
            'config' => [
                'viserio' => [
                    'connection' => [
                        'default'     => 'test',
                        'connections' => [],
                    ],
                ],
            ],
        ]));
        $manager->getConnection('fail');
    }

    public function testConnection(): void
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
                'connection' => [
                    'default'     => 'test',
                    'connections' => [
                        'test' => [],
                    ],
                ],
            ]);

        $manager = new TestConnectionManager(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));

        static::assertTrue($manager->getConnection());
        static::assertInternalType('array', $manager->getConnections());
    }

    public function testExtend(): void
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
                'connection' => [
                    'default'     => 'test',
                    'connections' => [
                        'test' => [],
                    ],
                ],
            ]);

        $manager = new TestConnectionManager(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));
        $manager->extend('test', function () {
            return new stdClass();
        });

        static::assertInstanceOf(stdClass::class, $manager->getConnection('test'));
    }

    public function testGetConnectionConfig(): void
    {
        $configArray = [
            'default'     => 'pdo',
            'connections' => [
                'pdo' => [
                    'servers' => 'localhost',
                ],
            ],
        ];
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'connection' => $configArray,
            ]);

        $manager = new TestConnectionManager(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));

        static::assertInternalType('array', $manager->getConnectionConfig('pdo'));
        static::assertSame($configArray, $manager->getConfig());
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
                'connection' => [
                    'default'     => 'foo',
                    'connections' => [
                        'foo' => ['driver'],
                    ],
                ],
            ]);

        $manager = new TestConnectionManager(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));

        static::assertSame([], $manager->getConnections());

        $return = $manager->getName();

        static::assertSame('manager', $return);
        static::assertArrayHasKey('foo', $manager->getConnections());
        static::assertTrue($manager->hasConnection('foo'));

        $manager->extend('call', function () {
            return new ArrayContainer();
        });
        $manager->setDefaultConnection('call');
        $manager->set('test', 'test');

        static::assertSame('test', $manager->get('test'));
    }

    public function testDefaultConnection(): void
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
                'connection' => [
                    'default'     => 'example',
                    'connections' => [],
                ],
            ]);

        $manager = new TestConnectionManager(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));

        static::assertSame('example', $manager->getDefaultConnection());

        $manager->setDefaultConnection('new');

        static::assertSame('new', $manager->getDefaultConnection());
    }

    public function testExtensionsConnection(): void
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
                'connection' => [
                    'default'     => 'stdClass2',
                    'connections' => [
                        'stdClass2' => [
                            'servers' => 'localhost',
                        ],
                    ],
                ],
            ]);

        $manager = new TestConnectionManager(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));
        $manager->extend('stdClass2', function ($options) {
            return new stdClass();
        });

        static::assertTrue($manager->hasConnection('stdClass2'));
        static::assertInstanceOf(stdClass::class, $manager->getConnection('stdClass2'));

        $manager->reconnect('stdClass2');

        static::assertInstanceOf(stdClass::class, $manager->getConnection('stdClass2'));
    }
}
