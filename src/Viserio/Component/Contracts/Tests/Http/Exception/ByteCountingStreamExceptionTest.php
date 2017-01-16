<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Tests\Http\Exception;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Viserio\Component\Contracts\Http\Exceptions\ByteCountingStreamException;

class ByteCountingStreamExceptionTest extends TestCase
{
    /**
     * @dataProvider getTestCases
     *
     * @param mixed $expect
     * @param mixed $actual
     */
    public function testCanGenerateByteCountingStreamException($expect, $actual)
    {
        $msg = 'The ByteCountingStream decorator expects to be able to '
            . "read {$expect} bytes from a stream, but the stream being decorated "
            . "only contains {$actual} bytes.";
        $prev      = new RuntimeException('prev');
        $exception = new ByteCountingStreamException($expect, $actual, $prev);

        self::assertEquals($msg, $exception->getMessage());
        self::assertSame($prev, $exception->getPrevious());
    }

    public function getTestCases()
    {
        return [[7, 5], [5, 0]];
    }
}
