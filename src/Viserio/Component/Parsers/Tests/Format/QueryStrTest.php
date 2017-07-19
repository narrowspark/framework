<?php
declare(strict_types=1);
namespace Viserio\Component\Parsers\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parsers\Dumper\QueryStrDumper;
use Viserio\Component\Parsers\Parser\QueryStrParser;

class QueryStrTest extends TestCase
{
    public function testParse(): void
    {
        $parsed = (new QueryStrParser())->parse('status=123&message=hello world');

        self::assertTrue(\is_array($parsed));
        self::assertSame(['status' => '123', 'message' => 'hello world'], $parsed);
    }

    public function testDump(): void
    {
        $expected = ['status' => 123, 'message' => 'hello world'];
        $payload  = \http_build_query($expected);
        $dump     = (new QueryStrDumper())->dump($expected);

        self::assertEquals($payload, $dump);
    }
}
