<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests\Displayer;

use Exception;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\HttpFactory\ResponseFactory;

/**
 * @internal
 */
final class HtmlDisplayerTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\Exception\Displayer\HtmlDisplayer
     */
    private $displayer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->displayer = new HtmlDisplayer(
            new ResponseFactory(),
            new ArrayContainer([
                'config' => [
                    'viserio' => [
                        'exception' => [
                            'http' => [
                                'html' => [
                                    'template_path' => __DIR__ . '/../../Resource/error.html',
                                ],
                            ],
                        ],
                    ],
                ],
            ])
        );
    }

    public function testServerError(): void
    {
        $response = $this->displayer->display(new Exception(), 'foo', 502, []);
        $expected = \file_get_contents(__DIR__ . '/../../Resource/error.html');
        $infos    = [
            'code'    => '502',
            'summary' => 'Houston, We Have A Problem.',
            'name'    => 'Bad Gateway',
            'detail'  => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.',
            'id'      => 'foo',
        ];

        foreach ($infos as $key => $val) {
            $expected = \str_replace("{{ $${key} }}", $val, $expected);
        }

        $this->assertSame($expected, (string) $response->getBody());
        $this->assertSame(502, $response->getStatusCode());
        $this->assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError(): void
    {
        $response = $this->displayer->display(new Exception(), 'bar', 404, []);
        $expected = \file_get_contents(__DIR__ . '/../../Resource/error.html');
        $infos    = [
            'code'    => '404',
            'summary' => 'Houston, We Have A Problem.',
            'name'    => 'Not Found',
            'detail'  => 'The requested resource could not be found but may be available again in the future.',
            'id'      => 'bar',
        ];

        foreach ($infos as $key => $val) {
            $expected = \str_replace("{{ $${key} }}", $val, $expected);
        }

        $this->assertSame($expected, (string) $response->getBody());
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties(): void
    {
        $exception = new Exception();

        $this->assertFalse($this->displayer->isVerbose());
        $this->assertTrue($this->displayer->canDisplay($exception, $exception, 500));
        $this->assertSame('text/html', $this->displayer->getContentType());
    }
}
