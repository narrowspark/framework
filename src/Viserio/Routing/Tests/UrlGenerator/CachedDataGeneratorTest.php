<?php
namespace Viserio\Routing\Test\UrlGenerator;

use Viserio\Filesystem\Filesystem;
use Viserio\Routing\UrlGenerator\CachedDataGenerator;

/**
 * CachedDataGeneratorTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
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
        $generator = $this->getMockForAbstractClass('Viserio\Contracts\Routing\DataGenerator');
        $generator->expects($expects ?: $this->any())
            ->method('getData')
            ->will($this->returnValue($routes));

        return new CachedDataGenerator(new Filesystem(), $generator, $filename, false);
    }
}
