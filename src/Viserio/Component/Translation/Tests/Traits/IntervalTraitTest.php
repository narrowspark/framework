<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Tests\Traits;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Translation\Traits\IntervalTrait;

class IntervalTraitTest extends TestCase
{
    use IntervalTrait;

    /**
     * @dataProvider getTests
     *
     * @param mixed $expected
     * @param mixed $number
     * @param mixed $interval
     */
    public function testIntervalTest($expected, $number, $interval)
    {
        self::assertEquals($expected, $this->intervalTest($number, $interval));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTestException()
    {
        $this->intervalTest(1, 'foobar');
    }

    public function getTests()
    {
        return [
            [true, 0, '{0}'],
            [true, 3, '{1,2, 3 ,4}'],
            [false, 10, '{1,2, 3 ,4}'],
            [false, 3, '[1,2]'],
            [true, 1, '[1,2]'],
            [true, 2, '[1,2]'],
            [false, 1, ']1,2['],
            [false, 2, ']1,2['],
            [true, log(0), '[-Inf,2['],
            [true, -log(0), '[-2,+Inf]'],
        ];
    }
}
