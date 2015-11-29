<?php
namespace Viserio\Translator\Tests\Traits;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.6
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Viserio\Translator\Traits\IntegerRuleTrait;

/**
 * IntegerRuleTraitTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6
 */
class IntegerRuleTraitTest extends \PHPUnit_Framework_TestCase
{
    use IntegerRuleTrait;

    protected $isInteger;

    protected $object;

    /**
     * @dataProvider provideIsInteger
     */
    public function testIsInt($value, $expected)
    {
        $actual = $this->isInteger($value);
        $this->assertSame($expected, $actual);
    }

    public function provideIsInteger()
    {
        return [
            'integer 0' => [0, true],
            'integer 1' => [1, true],
            'float 1.0' => [1.0, true],
            'string 1' => ['1', true],
            'string 1.0' => ['1.0', true],
            'float 1.1' => [1.1, false],
            'string 1.1' => ['1.1', false],
            'string z' => ['z', false],
        ];
    }
}
