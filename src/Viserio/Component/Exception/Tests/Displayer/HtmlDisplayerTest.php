<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\HttpFactory\ResponseFactory;
use Viserio\Component\HttpFactory\StreamFactory;

class HtmlDisplayerTest extends MockeryTestCase
{
    public function testServerError(): void
    {
        $displayer = $this->getDisplayer();
        $response  = $displayer->display(new Exception(), 'foo', 502, []);
        $expected  = \file_get_contents(__DIR__ . '/../../Resources/error.html');
        $infos     = [
            'code'    => '502',
            'summary' => 'Houston, We Have A Problem.',
            'name'    => 'Bad Gateway',
            'detail'  => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.',
            'id'      => 'foo',
        ];

        foreach ($infos as $key => $val) {
            $expected = \str_replace("{{ $$key }}", $val, $expected);
        }

        self::assertSame($expected, (string) $response->getBody());
        self::assertSame(502, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $displayer = $this->getDisplayer();
        $response  = $displayer->display(new Exception(), 'bar', 404, []);
        $expected  = \file_get_contents(__DIR__ . '/../../Resources/error.html');
        $infos     = [
            'code'    => '404',
            'summary' => 'Houston, We Have A Problem.',
            'name'    => 'Not Found',
            'detail'  => 'The requested resource could not be found but may be available again in the future.',
            'id'      => 'bar',
        ];

        foreach ($infos as $key => $val) {
            $expected = \str_replace("{{ $$key }}", $val, $expected);
        }

        self::assertSame($expected, (string) $response->getBody());
        self::assertSame(404, $response->getStatusCode());
        self::assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        $displayer = $this->getDisplayer();
        $exception = new Exception();

        self::assertFalse($displayer->isVerbose());
        self::assertTrue($displayer->canDisplay($exception, $exception, 500));
        self::assertSame('text/html', $displayer->contentType());
    }

    private function getDisplayer()
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
                'exception' => [
                    'template_path' => __DIR__ . '/../../Resources/error.html',
                ],
            ]);

        return new HtmlDisplayer(
            new ExceptionInfo(),
            new ResponseFactory(),
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );
    }
}
