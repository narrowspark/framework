<?php
namespace Viserio\Test\Translator\PluralCategorys;

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

/**
 * AbstractPluralCategorysTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.6-dev
 */
abstract class AbstractPluralCategorysTest extends \PHPUnit_Framework_TestCase
{
    protected $object;

    public function setUp()
    {
        parent::setUp();
        $class = preg_replace('#Test$#', '', get_class($this));
        $reflectionClass = new \ReflectionClass('\\Viserio\\Translator\\'.$class);
        $this->object = $reflectionClass->newInstanceArgs([]);
    }

    /**
     * @dataProvider category
     */
    public function testGetCategory($count, $expected)
    {
        $actual = $this->object->category($count);
        $this->assertEquals($expected, $actual);
    }

    abstract public function category();
}
