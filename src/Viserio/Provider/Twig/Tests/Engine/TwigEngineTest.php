<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests\Engine;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Viserio\Bridge\Twig\Extension\ConfigExtension;
use Viserio\Bridge\Twig\Extension\StrExtension;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Provider\Twig\Engine\TwigEngine;

class TwigEngineTest extends MockeryTestCase
{
    public function testGet(): void
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->times(3)
            ->with('viserio')
            ->andReturn([
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixtures/',
                        __DIR__,
                    ],
                    'engines'    => [
                        'twig' => [
                            'options' => [
                                'debug' => false,
                                'cache' => __DIR__ . '/../Cache',
                            ],
                        ],
                    ],
                ],
            ]);

        $engine = new TwigEngine(
            new Environment(
                new FilesystemLoader($config['viserio']['view']['paths']),
                $config['viserio']['view']['engines']['twig']['options']
            ),
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
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

        $this->delTree(__DIR__ . '/../Cache');
    }

    public function testAddTwigExtensions(): void
    {
        $repository = $this->mock(RepositoryContract::class);
        $repository->shouldReceive('has')
            ->once()
            ->with('view')
            ->andReturn(true);
        $config =  [
            'viserio' => [
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixtures/',
                    ],
                    'engines'    => [
                        'twig' => [
                            'options' => [
                                'debug' => false,
                                'cache' => __DIR__ . '/../Cache',
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
            new ArrayContainer([
                RepositoryContract::class => $config,
                ConfigExtension::class    => new ConfigExtension($repository),
            ])
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
    OK
</body>
</html>'), \trim($template));

        $this->delTree(__DIR__ . '/../Cache');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Twig extension [Viserio\Bridge\Twig\Extension\ConfigExtension] is not a object.
     */
    public function testTwigExtensionsToThrowException(): void
    {
        $config =  [
            'viserio' => [
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixtures/',
                    ],
                    'engines'    => [
                        'twig' => [
                            'options' => [
                                'debug' => false,
                                'cache' => __DIR__ . '/../Cache',
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
            new ArrayContainer([
                'config' => $config,
            ])
        );

        $engine->get([]);
    }

    private function delTree($dir)
    {
        $files = \array_diff(\scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (\is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : \unlink("$dir/$file");
        }

        return \rmdir($dir);
    }
}
