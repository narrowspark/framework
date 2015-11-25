<?php
namespace Viserio\View\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Viserio\View\Engines\EngineResolver;

/**
 * ViewEngineResolverTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class ViewEngineResolverTest extends \PHPUnit_Framework_TestCase
{
    public function testResolversMayBeResolved()
    {
        $resolver = new EngineResolver();
        $resolver->register('foo', function () { return new \StdClass(); });
        $result = $resolver->resolve('foo');
        $this->assertEquals(spl_object_hash($result), spl_object_hash($resolver->resolve('foo')));
    }

    public function testResolverThrowsExceptionOnUnknownEngine()
    {
        $this->setExpectedException('InvalidArgumentException');
        $resolver = new EngineResolver();
        $resolver->resolve('foo');
    }
}
