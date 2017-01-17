<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Formats;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parsers\Formats\QueryStr;

class QueryStrTest extends TestCase
{
    /**
     * @var \Viserio\Component\Parsers\Formats\QueryStr
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new QueryStr();
    }

    public function testParse()
    {
        $parsed = $this->parser->parse('status=123&message=hello world');

        self::assertTrue(is_array($parsed));
        self::assertSame(['status' => '123', 'message' => 'hello world'], $parsed);
    }

    public function testDump()
    {
        $expected = ['status' => 123, 'message' => 'hello world'];
        $payload  = http_build_query($expected);
        $dump     = $this->parser->dump($expected);

        self::assertEquals($payload, $dump);
    }
}
