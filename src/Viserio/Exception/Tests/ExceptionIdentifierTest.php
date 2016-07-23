<?php

declare(strict_types=1);
namespace Viserio\Exception\Tests;

use Exception;
use Viserio\Exception\ExceptionIdentifier;

class ExceptionIdentifierTest extends \PHPUnit_Framework_TestCase
{
    public function testIdentifyOne()
    {
        $i = new ExceptionIdentifier();
        $e = new Exception();

        $this->assertSame($i->identify($e), $i->identify($e));
    }

    public function testIdentifyTwo()
    {
        $i = new ExceptionIdentifier();
        $first = new Exception();
        $second = new Exception();

        $this->assertSame($i->identify($first), $i->identify($first));
        $this->assertSame($i->identify($second), $i->identify($second));
        $this->assertNotSame($i->identify($first), $i->identify($second));
    }

    public function testIdentifyMany()
    {
        $i = new ExceptionIdentifier();
        $arr = [];

        for ($j = 0; $j < 20; ++$j) {
            $arr[] = new Exception();
        }

        $ids = [];

        foreach ($arr as $e) {
            $ids[] = $i->identify($e);
        }

        // these should have been cleared
        $this->assertNotSame($i->identify($arr[0]), $ids[0]);
        $this->assertNotSame($i->identify($arr[2]), $ids[2]);
        $this->assertNotSame($i->identify($arr[5]), $ids[5]);

        // these should still be in memory
        $this->assertSame($i->identify($arr[7]), $ids[7]);
        $this->assertSame($i->identify($arr[15]), $ids[15]);
    }
}
