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

namespace Viserio\Component\Config\Tests\Container\Provider;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Config\Container\Pipeline\ResolveParameterPipe;
use Viserio\Component\Container\ContainerBuilder;

/**
 * @internal
 *
 * @covers \Viserio\Component\Config\Container\Pipeline\ResolveParameterPipe
 *
 * @small
 */
final class ResolveParameterPipeTest extends MockeryTestCase
{
    public function testProcess(): void
    {
        \putenv('TEST_NORMAL=teststring');
        \putenv('key=parameter value');
        \putenv('APP_URL=parameter');
        \putenv('string=string para');

        $container = new ContainerBuilder();

        $data = [
            'foo' => '{bar}',
            'bar' => 'test',
            'test' => 'TEST_NORMAL',
            'array' => [
                'baz' => '{env:TEST_NORMAL}',
            ],
            'multiArray' => [
                'disks' => [
                    'local' => [
                        'driver' => 'local',
                        'root' => 'd',
                    ],
                    'public' => [
                        'driver' => 'local',
                        'root' => '',
                        'url' => '{env:APP_URL}',
                        'visibility' => [
                            'test' => '{env:key}',
                        ],
                    ],
                ],
                'string' => '{env:string}',
            ],
        ];

        foreach ($data as $key => $value) {
            $container->setParameter($key, $value);
        }

        $this->process($container);

        self::assertSame('test', $container->getParameter('bar')->getValue());
        self::assertSame(['baz' => 'teststring'], $container->getParameter('array')->getValue());
        self::assertSame([
            'disks' => [
                'local' => [
                    'driver' => 'local',
                    'root' => 'd',
                ],
                'public' => [
                    'driver' => 'local',
                    'root' => '',
                    'url' => 'parameter',
                    'visibility' => [
                        'test' => 'parameter value',
                    ],
                ],
            ],
            'string' => 'string para',
        ], $container->getParameter('multiArray')->getValue());

        \putenv('TEST_NORMAL=');
        \putenv('TEST_NORMAL');
        \putenv('key=');
        \putenv('key');
        \putenv('APP_URL=');
        \putenv('APP_URL');
        \putenv('string=');
        \putenv('string');
    }

    /**
     * @param ContainerBuilder $container
     */
    private function process(ContainerBuilder $container): void
    {
        $pipe = new ResolveParameterPipe();

        $pipe->process($container);
    }
}
