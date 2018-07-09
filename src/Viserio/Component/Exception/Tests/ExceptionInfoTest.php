<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\ExceptionInfo;

/**
 * @internal
 */
final class ExceptionInfoTest extends TestCase
{
    public function testBadError(): void
    {
        $info = ExceptionInfo::generate('test', 666);

        $expected = [
            'id'      => 'test',
            'code'    => 500,
            'name'    => 'Internal Server Error',
            'detail'  => 'An error has occurred and this resource cannot be displayed.',
            'summary' => 'Houston, We Have A Problem.',
        ];

        static::assertSame($expected, $info);
    }

    public function testHiddenError(): void
    {
        $info = ExceptionInfo::generate('hi', 503);

        $expected = [
            'id'      => 'hi',
            'code'    => 503,
            'name'    => 'Service Unavailable',
            'detail'  => 'The server is currently unavailable. It may be overloaded or down for maintenance.',
            'summary' => 'Houston, We Have A Problem.',
        ];

        static::assertSame($expected, $info);
    }
}
