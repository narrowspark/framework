<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Support\Tests\Fixture\TestConnectionManager;

class AbstractConnectionManagerTest extends MockeryTestCase
{
    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Connection [fail] not supported.
     */
    public function testConnectionToThrowRuntimeException(): void
    {
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

        self::assertTrue($manager->getConnection());
        self::assertTrue(\is_array($manager->getConnections()));
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

        self::assertInstanceOf(stdClass::class, $manager->getConnection('test'));
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

        self::assertTrue(\is_array($manager->getConnectionConfig('pdo')));
        self::assertSame($configArray, $manager->getConfig());
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

        self::assertSame([], $manager->getConnections());

        $return = $manager->getName();

        self::assertSame('manager', $return);
        self::assertArrayHasKey('foo', $manager->getConnections());
        self::assertTrue($manager->hasConnection('foo'));

        $manager->extend('call', function () {
            return new ArrayContainer();
        });
        $manager->setDefaultConnection('call');
        $manager->set('test', 'test');

        self::assertSame('test', $manager->get('test'));
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

        self::assertSame('example', $manager->getDefaultConnection());

        $manager->setDefaultConnection('new');

        self::assertSame('new', $manager->getDefaultConnection());
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

        self::assertTrue($manager->hasConnection('stdClass2'));
        self::assertInstanceOf(stdClass::class, $manager->getConnection('stdClass2'));

        $manager->reconnect('stdClass2');

        self::assertInstanceOf(stdClass::class, $manager->getConnection('stdClass2'));
    }
}
