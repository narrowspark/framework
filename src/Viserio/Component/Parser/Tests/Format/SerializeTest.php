<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\SerializeDumper;
use Viserio\Component\Parser\Parser\SerializeParser;

class SerializeTest extends TestCase
{
    public function testParse(): void
    {
        $parsed = (new SerializeParser())->parse('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}');

        self::assertTrue(\is_array($parsed));
        self::assertSame(['status' => 123, 'message' => 'hello world'], $parsed);
    }

    /**
     * @expectedException \Viserio\Component\Contract\Parser\Exception\ParseException
     */
    public function testParseToThrowException(): void
    {
        (new SerializeParser())->parse('asdgfg<-.<fsdw|df>24hg2=');
    }

    public function testDump(): void
    {
        $dump = (new SerializeDumper())->dump(['status' => 123, 'message' => 'hello world']);

        self::assertEquals('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}', $dump);
    }
}
