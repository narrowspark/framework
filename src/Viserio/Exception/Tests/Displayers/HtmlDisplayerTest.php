<?php
declare(strict_types=1);
namespace Viserio\Exception\Tests\Displayers;

use Exception;
use InvalidArgumentException;
use Viserio\Exception\{
    Displayers\HtmlDisplayer,
    ExceptionInfo
};

class HtmlDisplayerTest extends \PHPUnit_Framework_TestCase
{
    public function testServerError()
    {
        $file = __DIR__.'/../../Resources/error.html';
        $displayer = new HtmlDisplayer(new ExceptionInfo(), $file);
        $response = $displayer->display(new Exception(), 'foo', 502, []);
        $expected = file_get_contents($file);
        $infos = [
            'code' => '502',
            'summary' => 'Houston, We Have A Problem.',
            'name' => 'Bad Gateway',
            'detail' => 'The server was acting as a gateway or proxy and received an invalid response from the upstream server.',
            'id' => 'foo',
        ];

        foreach ($infos as $key => $val) {
            $expected = str_replace("{{ $$key }}", $val, $expected);
        }

        $this->assertSame($expected, (string) $response->getBody());
        $this->assertSame(502, $response->getStatusCode());
        $this->assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testClientError()
    {
        $file = __DIR__.'/../../Resources/error.html';
        $displayer = new HtmlDisplayer(new ExceptionInfo(), $file);
        $response = $displayer->display(new Exception(), 'bar', 404, []);
        $expected = file_get_contents($file);
        $infos = [
            'code' => '404',
            'summary' => 'Houston, We Have A Problem.',
            'name' => 'Not Found',
            'detail' => 'The requested resource could not be found but may be available again in the future.',
            'id' => 'bar',
        ];

        foreach ($infos as $key => $val) {
            $expected = str_replace("{{ $$key }}", $val, $expected);
        }

        $this->assertSame($expected, (string) $response->getBody());
        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('text/html', $response->getHeaderLine('Content-Type'));
    }

    public function testProperties()
    {
        $file = __DIR__.'/../../Resources/error.html';
        $displayer = new HtmlDisplayer(new ExceptionInfo(), $file);
        $exception = new Exception();

        $this->assertFalse($displayer->isVerbose());
        $this->assertTrue($displayer->canDisplay($exception, $exception, 500));
        $this->assertSame('text/html', $displayer->contentType());
    }
}
