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

use Viserio\Translator\PluralCategorys\Irish;

/**
 * IrishTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
class IrishTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        parent::setUp();
        $this->object = new Irish();
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
            [2, 'two'],
            ['2', 'two'],
            [2.0, 'two'],
            ['2.0', 'two'],
            [3, 'few'],
            ['3', 'few'],
            [3.0, 'few'],
            ['3.0', 'few'],
            [4, 'few'],
            [5, 'few'],
            [6, 'few'],
            [7, 'many'],
            ['7', 'many'],
            [7.0, 'many'],
            ['7.0', 'many'],
            [8, 'many'],
            [9, 'many'],
            [10, 'many'],
            [0, 'other'],
            [11, 'other'],
            [77, 'other'],
            [301, 'other'],
            [999, 'other'],
            [1.2, 'other'],
            [2.07, 'other'],
            [3.94, 'other'],
            [7.81, 'other'],
            [11.68, 'other'],
        ];
    }
}
