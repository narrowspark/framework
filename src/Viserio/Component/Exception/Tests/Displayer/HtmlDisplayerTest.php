<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\ExceptionInfo;
use Viserio\Component\HttpFactory\ResponseFactory;

class HtmlDisplayerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Exception\Displayer\HtmlDisplayer
     */
    private $displayer;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
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

        $this->displayer = new HtmlDisplayer(
            new ExceptionInfo(),
            new ResponseFactory(),
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );
    }

    public function testServerError(): void
    {
        $response  = $this->displayer->display(new Exception(), 'foo', 502, []);
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
        $response  = $this->displayer->display(new Exception(), 'bar', 404, []);
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
        $exception = new Exception();

        self::assertFalse($this->displayer->isVerbose());
        self::assertTrue($this->displayer->canDisplay($exception, $exception, 500));
        self::assertSame('text/html', $this->displayer->getContentType());
    }
}
