<?php

namespace Brainwave\View\Test;

/*
 * Narrowspark - a PHP 5 framework
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 * @link        http://www.narrowspark.de
 * @license     http://www.narrowspark.com/license
 * @version     0.9.8-dev
 * @package     Narrowspark/framework
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

use Brainwave\View\ViewFinder;
use Mockery as Mock;

/**
 * ViewTest.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5-dev
 */
class ViewFinderTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }
    public function testBasicViewFinding()
    {
        $finder = $this->getFinder();
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.plates.php')->andReturn(true);
        $this->assertEquals(__DIR__.'/foo.plates.php', $finder->find('foo'));
    }
    public function testCascadingFileLoading()
    {
        $finder = $this->getFinder();
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.plates.php')->andReturn(false);
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.php')->andReturn(true);
        $this->assertEquals(__DIR__.'/foo.php', $finder->find('foo'));
    }
    public function testDirectoryCascadingFileLoading()
    {
        $finder = $this->getFinder();
        $finder->addLocation(__DIR__.'/nested');
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.plates.php')->andReturn(false);
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.php')->andReturn(false);
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/nested/foo.plates.php')->andReturn(true);
        $this->assertEquals(__DIR__.'/nested/foo.plates.php', $finder->find('foo'));
    }
    public function testNamespacedBasicFileLoading()
    {
        $finder = $this->getFinder();
        $finder->addNamespace('foo', __DIR__.'/foo');
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.plates.php')->andReturn(true);
        $this->assertEquals(__DIR__.'/foo/bar/baz.plates.php', $finder->find('foo::bar.baz'));
    }
    public function testCascadingNamespacedFileLoading()
    {
        $finder = $this->getFinder();
        $finder->addNamespace('foo', __DIR__.'/foo');
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.plates.php')->andReturn(false);
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.php')->andReturn(true);
        $this->assertEquals(__DIR__.'/foo/bar/baz.php', $finder->find('foo::bar.baz'));
    }
    public function testDirectoryCascadingNamespacedFileLoading()
    {
        $finder = $this->getFinder();
        $finder->addNamespace('foo', [__DIR__.'/foo', __DIR__.'/bar']);
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.plates.php')->andReturn(false);
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo/bar/baz.php')->andReturn(false);
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/bar/bar/baz.plates.php')->andReturn(true);
        $this->assertEquals(__DIR__.'/bar/bar/baz.plates.php', $finder->find('foo::bar.baz'));
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionThrownWhenViewNotFound()
    {
        $finder = $this->getFinder();
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.plates.php')->andReturn(false);
        $finder->getFilesystem()->shouldReceive('exists')->once()->with(__DIR__.'/foo.php')->andReturn(false);
        $finder->find('foo');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionThrownOnInvalidViewName()
    {
        $finder = $this->getFinder();
        $finder->find('name::');
    }
    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionThrownWhenNoHintPathIsRegistered()
    {
        $finder = $this->getFinder();
        $finder->find('name::foo');
    }
    public function testAddingExtensionPrependsNotAppends()
    {
        $finder = $this->getFinder();
        $finder->addExtension('baz');
        $extensions = $finder->getExtensions();
        $this->assertEquals('baz', reset($extensions));
    }
    public function testAddingExtensionsReplacesOldOnes()
    {
        $finder = $this->getFinder();
        $finder->addExtension('baz');
        $finder->addExtension('baz');
        $this->assertCount(3, $finder->getExtensions());
    }
    public function testPassingViewWithHintReturnsTrue()
    {
        $finder = $this->getFinder();
        $this->assertTrue($finder->hasHintInformation('hint::foo.bar'));
    }
    public function testPassingViewWithoutHintReturnsFalse()
    {
        $finder = $this->getFinder();
        $this->assertFalse($finder->hasHintInformation('foo.bar'));
    }
    public function testPassingViewWithFalseHintReturnsFalse()
    {
        $finder = $this->getFinder();
        $this->assertFalse($finder->hasHintInformation('::foo.bar'));
    }
    protected function getFinder()
    {
        return new ViewFinder(Mock::mock('Brainwave\Filesystem\Filesystem'), [__DIR__]);
    }
}
