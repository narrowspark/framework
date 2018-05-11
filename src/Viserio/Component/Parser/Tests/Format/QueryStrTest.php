<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\QueryStrDumper;
use Viserio\Component\Parser\Parser\QueryStrParser;

class QueryStrTest extends TestCase
{
    public function testParse(): void
    {
        $parsed = (new QueryStrParser())->parse('status=123&message=hello world');

        self::assertInternalType('array', $parsed);
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
