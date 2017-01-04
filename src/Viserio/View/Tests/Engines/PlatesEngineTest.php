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
        $uri = $this->mock(UriInterface::class);
        $request = $this->mock(ServerRequestInterface::class);
        $request->shouldReceive('getUri')
            ->once()
            ->andReturn($uri);

        $engine = new PlatesEngine(
            [
                'template' => [
                    'default' => __DIR__ . '/../Fixture/',
                ],
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
}
