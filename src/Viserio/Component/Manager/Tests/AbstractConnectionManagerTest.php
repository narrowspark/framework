<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Manager\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use stdClass;
use Viserio\Component\Manager\Tests\Fixture\TestConnectionManager;
use Viserio\Contract\Manager\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class AbstractConnectionManagerTest extends MockeryTestCase
{
    public function testConnectionToThrowRuntimeException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Connection [fail] is not supported.');

        $manager = new TestConnectionManager([
            'viserio' => [
                'connection' => [
                    'default' => 'test',
                    'connections' => [],
                ],
            ],
        ]);
        $manager->getConnection('fail');
    }

    public function testConnection(): void
    {
        $manager = new TestConnectionManager([
            'viserio' => [
                'connection' => [
                    'default' => 'test',
                    'connections' => [
                        'test' => [],
                    ],
                ],
            ],
        ]);

        self::assertTrue($manager->getConnection());
    }

    public function testExtend(): void
    {
        $manager = new TestConnectionManager([
            'viserio' => [
                'connection' => [
                    'default' => 'test',
                    'connections' => [
                        'test' => [],
                    ],
                ],
            ],
        ]);
        $manager->extend('test', function () {
            return new stdClass();
        });

        self::assertInstanceOf(stdClass::class, $manager->getConnection('test'));
    }

    public function testGetConnectionConfig(): void
    {
        $configArray = [
            'default' => 'pdo',
            'connections' => [
                'pdo' => [
                    'servers' => 'localhost',
                ],
            ],
        ];

        $manager = new TestConnectionManager([
            'viserio' => [
                'connection' => $configArray,
            ],
        ]);

        $manager->getConnectionConfig('pdo');

        self::assertSame($configArray, $manager->getConfig());
    }

    public function testCall(): void
    {
        $manager = new TestConnectionManager([
            'viserio' => [
                'connection' => [
                    'default' => 'foo',
                    'connections' => [
                        'foo' => ['driver'],
                    ],
                ],
            ],
        ]);

        self::assertSame([], $manager->getConnections());

        $return = $manager->getName();

        self::assertSame('manager', $return);
        self::assertArrayHasKey('foo', $manager->getConnections());
        self::assertTrue($manager->hasConnection('foo'));

        $manager->extend('call', function () {
            return new ArrayContainer([]);
        });
        $manager->setDefaultConnection('call');
        $manager->set('test', 'test');

        self::assertSame('test', $manager->get('test'));
    }

    public function testDefaultConnection(): void
    {
        $manager = new TestConnectionManager([
            'viserio' => [
                'connection' => [
                    'default' => 'example',
                    'connections' => [],
                ],
            ],
        ]);

        self::assertSame('example', $manager->getDefaultConnection());

        $manager->setDefaultConnection('new');

        self::assertSame('new', $manager->getDefaultConnection());
    }

    public function testExtensionsConnection(): void
    {
        $manager = new TestConnectionManager([
            'viserio' => [
                'connection' => [
                    'default' => 'stdClass2',
                    'connections' => [
                        'stdClass2' => [
                            'servers' => 'localhost',
                        ],
                    ],
                ],
            ],
        ]);
        $manager->extend('stdClass2', function ($options) {
            return new stdClass();
        });

        self::assertTrue($manager->hasConnection('stdClass2'));
        self::assertInstanceOf(stdClass::class, $manager->getConnection('stdClass2'));

        $manager->reconnect('stdClass2');

        self::assertInstanceOf(stdClass::class, $manager->getConnection('stdClass2'));
    }
}
