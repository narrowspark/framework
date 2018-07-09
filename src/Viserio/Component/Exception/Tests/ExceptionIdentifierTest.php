<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\ExceptionIdentifier;

/**
 * @internal
 */
final class ExceptionIdentifierTest extends TestCase
{
    public function testIdentifyOne(): void
    {
        $e = new Exception();

        static::assertSame(ExceptionIdentifier::identify($e), ExceptionIdentifier::identify($e));
    }

    public function testIdentifyTwo(): void
    {
        $first  = new Exception();
        $second = new Exception();

        static::assertSame(ExceptionIdentifier::identify($first), ExceptionIdentifier::identify($first));
        static::assertSame(ExceptionIdentifier::identify($second), ExceptionIdentifier::identify($second));
        static::assertNotSame(ExceptionIdentifier::identify($first), ExceptionIdentifier::identify($second));
    }

    public function testIdentifyMany(): void
    {
        $arr = [];

        for ($j = 0; $j < 20; $j++) {
            $arr[] = new Exception();
        }

        $ids = [];

        foreach ($arr as $e) {
            $ids[] = ExceptionIdentifier::identify($e);
        }

        // these should have been cleared
        static::assertNotSame(ExceptionIdentifier::identify($arr[0]), $ids[0]);
        static::assertNotSame(ExceptionIdentifier::identify($arr[2]), $ids[2]);
        static::assertNotSame(ExceptionIdentifier::identify($arr[5]), $ids[5]);

        // these should still be in memory
        static::assertSame(ExceptionIdentifier::identify($arr[7]), $ids[7]);
        static::assertSame(ExceptionIdentifier::identify($arr[15]), $ids[15]);
    }
}
