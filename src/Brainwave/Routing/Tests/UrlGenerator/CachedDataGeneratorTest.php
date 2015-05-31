<?php

namespace Brainwave\Routing\Test\UrlGenerator;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.10.0-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\Filesystem\Filesystem;
use Brainwave\Routing\UrlGenerator\CachedDataGenerator;

/**
 * CachedDataGeneratorTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class CachedDataGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test basic caching.
     */
    public function testCreateWritesCache()
    {
        $filename = sys_get_temp_dir().'/_collector'.sha1(uniqid('_collector', true));
        $generator = $this->getGenerator($filename, $this->once());
        $data = $generator->getData();
        $this->assertNotEmpty(file_get_contents($filename));
        $this->assertEquals([], $data);

        return $filename;
    }

    /**
     * Test create with a fresh cache.
     *
     * @depends testCreateWritesCache
     */
    public function testCreateWithFreshCache($filename)
    {
        $generator = $this->getGenerator($filename, $this->never());
        $generator->getData();
    }

    /**
     * Test an unwriteable file.
     *
     * @todo This relies on something outside of Narrowspark throwing the exception
     */
    public function testUnableToWriteCache()
    {
        $generator = $this->getGenerator('/some/unwriteable/path');
        $this->setExpectedException('RuntimeException', 'Failed to create');
        $generator->getData();
    }

    /**
     * @param string $filename
     * @param null   $expects
     * @param array  $routes
     *
     * @return CachedDataGenerator
     */
    protected function getGenerator($filename, $expects = null, $routes = [])
    {
        $generator = $this->getMockForAbstractClass('Brainwave\Contracts\Routing\DataGenerator');
        $generator->expects($expects ?: $this->any())
            ->method('getData')
            ->will($this->returnValue($routes));

        return new CachedDataGenerator(new Filesystem(), $generator, $filename, false);
    }
}
