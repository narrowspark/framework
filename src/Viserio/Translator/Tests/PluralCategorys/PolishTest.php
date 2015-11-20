<?php
namespace Viserio\Translator\Tests\PluralCategorys;

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

use Viserio\Translator\PluralCategorys\Polish;

/**
 * PolishTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class PolishTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new Polish();
    }

    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = $this->object->category($count);
        $this->assertEquals($expected, $actual);
    }

    public function category()
    {
        return [
            [1, 'one'],
            ['1', 'one'],
            [1.0, 'one'],
            ['1.0', 'one'],
            [2, 'few'],
            ['2', 'few'],
            [2.0, 'few'],
            ['2.0', 'few'],
            [3, 'few'],
            [4, 'few'],
            [32, 'few'],
            [33, 'few'],
            [34, 'few'],
            [102, 'few'],
            [103, 'few'],
            [104, 'few'],
            [0, 'other'],
            [5, 'other'],
            [6, 'other'],
            [10, 'other'],
            [12, 'other'],
            [15, 'other'],
            [20, 'other'],
            [21, 'other'],
            [22, 'other'],
            [23, 'other'],
            [24, 'other'],
            [25, 'other'],
            [30, 'other'],
            [31, 'other'],
            [47, 'other'],
            [1.31, 'other'],
            [2.31, 'other'],
            [5.31, 'other'],
        ];
    }
}
