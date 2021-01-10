<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Provider\Twig\Tests\Engine;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Contract\View\Exception\RuntimeException;
use Viserio\Provider\Twig\Engine\TwigEngine;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class TwigEngineTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        $dir = \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Cache';

        \array_map(static function ($value): void {
            @\unlink($value);
        }, \glob($dir . \DIRECTORY_SEPARATOR . '*', \GLOB_NOSORT));

        @\rmdir($dir);
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
            )
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
            [
                new StrExtension(),
                // @todo use container to find twig extensions
            ]
        );

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
</body>
</html>'), \trim($template));
    }

    public function testTwigExtensionsToThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Twig extension [Viserio\\Bridge\\Twig\\Extension\\StrExtension] is not a object.');

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
            [
                StrExtension::class,
            ]
        );

        $engine->get([]);
    }
}
