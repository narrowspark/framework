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

namespace Viserio\Provider\Twig\Tests\Engine;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Contract\Config\Repository as RepositoryContract;
use Viserio\Contract\View\Exception\RuntimeException;
use Viserio\Provider\Twig\Engine\TwigEngine;

/**
 * @internal
 *
 * @small
 */
final class TwigEngineTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        $dir = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Cache';

        if (\is_dir($dir)) {
            (new Filesystem())->remove($dir);
        }
    }

    public function testGet(): void
    {
        $config = [
            'viserio' => [
                'view' => [
                    'paths' => [
                        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR,
                        __DIR__,
                    ],
                    'engines' => [
                        'twig' => [
                            'options' => [
                                'debug' => false,
                                'cache' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Cache',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $engine = new TwigEngine(
            new Environment(
                new FilesystemLoader($config['viserio']['view']['paths']),
                $config['viserio']['view']['engines']['twig']['options']
            ),
            $config
        );

        $template = $engine->get(['name' => 'twightml.twig.html']);

        self::assertSame(\trim('<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <link rel="stylesheet" href="">
</head>
<body>
    hallo
</body>
</html>'), \trim($template));
    }

    public function testAddTwigExtensions(): void
    {
        $repository = \Mockery::mock(RepositoryContract::class);
        $repository->shouldReceive('has')
            ->once()
            ->with('view')
            ->andReturn(true);
        $config = [
            'viserio' => [
                'view' => [
                    'paths' => [
                        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR,
                    ],
                    'engines' => [
                        'twig' => [
                            'options' => [
                                'debug' => false,
                                'cache' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Cache',
                            ],
                            'extensions' => [
                                new StrExtension(),
                                ConfigExtension::class,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $engine = new TwigEngine(
            new Environment(
                new FilesystemLoader($config['viserio']['view']['paths']),
                $config['viserio']['view']['engines']['twig']['options']
            ),
            $config
        );
        $engine->setContainer(new ArrayContainer([
            ConfigExtension::class => new ConfigExtension($repository),
        ]));

        $template = $engine->get(['name' => 'twightml2.twig.html']);

        self::assertEquals(\trim('<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title></title>
    <link rel="stylesheet" href="">
</head>
<body>
    test_t_e_s_t
    OK
</body>
</html>'), \trim($template));
    }

    public function testTwigExtensionsToThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Twig extension [Viserio\\Bridge\\Twig\\Extension\\ConfigExtension] is not a object.');

        $config = [
            'viserio' => [
                'view' => [
                    'paths' => [
                        \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR,
                    ],
                    'engines' => [
                        'twig' => [
                            'options' => [
                                'debug' => false,
                                'cache' => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Cache',
                            ],
                            'extensions' => [
                                ConfigExtension::class,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $engine = new TwigEngine(
            new Environment(
                new FilesystemLoader($config['viserio']['view']['paths']),
                $config['viserio']['view']['engines']['twig']['options']
            ),
            $config
        );

        $engine->get([]);
    }
}
