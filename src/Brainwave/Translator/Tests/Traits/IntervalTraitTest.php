<?php

namespace Brainwave\Test\Translator\Traits;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.6-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Translator\Traits\IntervalTrait;

/**
 * IntervalTraitTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class IntervalTraitTest extends \PHPUnit_Framework_TestCase
{
    use IntervalTrait;

    /**
     * @dataProvider getTests
     */
    public function testTest($expected, $number, $interval)
    {
        $this->assertEquals($expected, $this->test($number, $interval));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testTestException()
    {
        $this->test(1, 'foobar');
    }

    public function getTests()
    {
        return [
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
