<?php
declare(strict_types=1);
namespace Viserio\Component\Parser\Tests\Format;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Parser\Dumper\SerializeDumper;
use Viserio\Component\Parser\Parser\SerializeParser;

/**
 * @internal
 */
final class SerializeTest extends TestCase
{
    public function testParse(): void
    {
        $parsed = (new SerializeParser())->parse('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}');

        $this->assertInternalType('array', $parsed);
        $this->assertSame(['status' => 123, 'message' => 'hello world'], $parsed);
    }

    public function testParseToThrowException(): void
    {
        $this->expectException(\Viserio\Component\Contract\Parser\Exception\ParseException::class);

        (new SerializeParser())->parse('asdgfg<-.<fsdw|df>24hg2=');
    }

    public function testDump(): void
    {
        $dump = (new SerializeDumper())->dump(['status' => 123, 'message' => 'hello world']);

        $this->assertEquals('a:2:{s:6:"status";i:123;s:7:"message";s:11:"hello world";}', $dump);
    }
}
