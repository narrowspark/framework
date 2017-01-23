<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests\Engines;

use League\Plates\Extension\Asset;
use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\Component\View\Engines\PlatesEngine;

class PlatesEngineTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

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
            ->once()
            ->with('viserio')
            ->andReturn([
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixture/',
                        __DIR__,
                    ],
                    'extensions' => ['phtml', 'php'],
                    'engines' => [
                        'plates' => [
                        ],
                    ],
                ],
            ]);

        $engine = new PlatesEngine(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));

        $template = $engine->get(['name' => 'plates.php']);

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

    public function testGetWithExtensions()
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
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixture/',
                        __DIR__,
                    ],
                    'extensions' => ['phtml', 'php'],
                    'engines' => [
                        'plates' => [
                            'extensions' => [
                                new Asset(__DIR__),
                            ],
                        ],
                    ],
                ],
            ]);

        $engine = new PlatesEngine(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));

        $template = $engine->get(['name' => 'plates.php']);

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

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Plates extension [0] is not a object.
     */
    public function testGetWithExtensionsThrowException()
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
                'view' => [
                    'paths'      => [
                        __DIR__ . '/../Fixture/',
                        __DIR__,
                    ],
                    'extensions' => ['phtml', 'php'],
                    'engines' => [
                        'plates' => [
                            'extensions' => [0],
                        ],
                    ],
                ],
            ]);

        $engine = new PlatesEngine(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));

        $engine->get(['name' => 'plates.php']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Template [plates.php] dont exist!
     */
    public function testGetThrowExceptionOnFileDontExist()
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
                'view' => [
                    'paths'      => [
                        __DIR__,
                    ],
                    'extensions' => ['phtml', 'php'],
                    'engines' => [
                        'plates' => [
                        ],
                    ],
                ],
            ]);

        $engine = new PlatesEngine(new ArrayContainer([
            RepositoryContract::class => $config,
        ]));

        $engine->get(['name' => 'plates.php']);
    }
}
