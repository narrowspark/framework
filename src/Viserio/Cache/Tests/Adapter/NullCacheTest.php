<?php
namespace Viserio\Cache\Test\Adapter;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

use Viserio\Cache\Adapter\NullCache;

/**
 * NullCacheTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
class NullCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testItemsCanNotBeCached()
    {
        $store = new NullCache();
        $store->put('foo', 'bar', 10);
        $this->assertNull($store->get('foo'));
    }

    public function testGetMultipleReturnsMultipleNulls()
    {
        $store = new NullStore;

        $this->assertEquals([
            'foo'   => null,
            'bar'   => null,
        ], $store->getMultiple([
            'foo',
            'bar',
        ]));
    }
}
