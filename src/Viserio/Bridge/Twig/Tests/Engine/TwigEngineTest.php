<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests\Engine;

use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Viserio\Bridge\Twig\Engine\TwigEngine;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\OptionsResolver\ComponentOptionsResolver;

class TwigEngineTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->delTree(__DIR__ . '/../Cache');

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testGet()
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
                    'extensions' => ['phtml', 'php'],
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

        $engine = new TwigEngine(new ArrayContainer([
            RepositoryContract::class       => $config,
            ComponentOptionsResolver::class => new ComponentOptionsResolver(),
            Twig_Environment::class         => new Twig_Environment(
                new Twig_Loader_Filesystem($config['viserio']['view']['paths']),
                $config['viserio']['view']['engines']['twig']['options']
            ),
        ]));

        $template = $engine->get(['name' => 'twightml.twig.html']);

        static::assertSame(trim('<!DOCTYPE html>
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
</html>'), trim($template));
    }

    private function delTree($dir)
    {
        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }

        return rmdir($dir);
    }
}
