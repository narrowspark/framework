<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Tests;

use Exception;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Exception\ExceptionIdentifier;

class ExceptionIdentifierTest extends TestCase
{
    public function testIdentifyOne()
    {
        $i = new ExceptionIdentifier();
        $e = new Exception();

        self::assertSame($i->identify($e), $i->identify($e));
    }

    public function testIdentifyTwo()
    {
        $i      = new ExceptionIdentifier();
        $first  = new Exception();
        $second = new Exception();

        self::assertSame($i->identify($first), $i->identify($first));
        self::assertSame($i->identify($second), $i->identify($second));
        self::assertNotSame($i->identify($first), $i->identify($second));
    }

    public function testIdentifyMany()
    {
        $i   = new ExceptionIdentifier();
        $arr = [];

        for ($j = 0; $j < 20; ++$j) {
            $arr[] = new Exception();
        }

        $ids = [];

        foreach ($arr as $e) {
            $ids[] = $i->identify($e);
        }

        // these should have been cleared
        self::assertNotSame($i->identify($arr[0]), $ids[0]);
        self::assertNotSame($i->identify($arr[2]), $ids[2]);
        self::assertNotSame($i->identify($arr[5]), $ids[5]);

        // these should still be in memory
        self::assertSame($i->identify($arr[7]), $ids[7]);
        self::assertSame($i->identify($arr[15]), $ids[15]);
    }
}
