<?php
declare(strict_types=1);
namespace Viserio\View\Tests\Engines;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Viserio\View\Engines\PlatesEngine;

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
        $engine = new PlatesEngine(
            [
                'template' => [
                    'default' => __DIR__ . '/../Fixture/',
                ],
            ]
        );

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
        $uri = $this->mock(UriInterface::class);
        $uri->shouldReceive('getPath')
            ->once()
            ->andReturn('');
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn($uri);

        $engine = new PlatesEngine(
            [
                'template' => [
                    'default' => __DIR__ . '/../Fixture/'
                ],
                'engine' => [
                    'plates' => [
                        'asset' => __DIR__
                    ]
                ]
            ],
            $request
        );

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
     * @expectedExceptionMessage Plates extension [0 => integer] is not a object.
     */
    public function testGetWithExtensionsThrowException()
    {
        $engine = new PlatesEngine(
            [
                'template' => [
                    'default' => __DIR__ . '/../Fixture/',
                ],
                'engine' => [
                    'plates' => [
                        'extensions' => [
                            0
                        ]
                    ]
                ]
            ]
        );

        $engine->get(['name' => 'plates.php']);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Template [plates.php] dont exist!
     */
    public function testGetThrowExceptionOnFileDontExist()
    {
        $engine = new PlatesEngine(
            [
                'template' => [
                    'default' => __DIR__,
                ],
            ]
        );

        $engine->get(['name' => 'plates.php']);
    }
}
