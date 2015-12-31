<?php
namespace Viserio\View\Test;

use Mockery as Mock;
use Viserio\View\ViewFinder;
use Viserio\Filesystem\Filesystem;

class ViewFinderTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testBasicViewFinding()
    {
        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo.php')
            ->andReturn(true);

        $this->assertEquals($this->getPath() . '/foo.php', $finder->find('foo'));
    }

    public function testCascadingFileLoading()
    {
        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo.php')
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo.php')
            ->andReturn(true);

        $this->assertEquals($this->getPath() . '/foo.php', $finder->find('foo'));
    }

    public function testDirectoryCascadingFileLoading()
    {
        $finder = $this->getFinder();
        $finder->addLocation($this->getPath() . '/Nested');
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo.php')
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo.php')
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/Nested/foo.php')
            ->andReturn(true);

        $this->assertEquals($this->getPath() . '/Nested/foo.php', $finder->find('foo'));
    }

    public function testNamespacedBasicFileLoading()
    {
        $finder = $this->getFinder();
        $finder->addNamespace('foo', $this->getPath() . '/foo');
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo/bar/baz.php')
            ->andReturn(true);

        $this->assertEquals($this->getPath() . '/foo/bar/baz.php', $finder->find('foo::bar.baz'));
    }

    public function testCascadingNamespacedFileLoading()
    {
        $finder = $this->getFinder();
        $finder->addNamespace('foo', $this->getPath() . '/foo');
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo/bar/baz.php')
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo/bar/baz.php')
            ->andReturn(true);

        $this->assertEquals($this->getPath() . '/foo/bar/baz.php', $finder->find('foo::bar.baz'));
    }

    public function testDirectoryCascadingNamespacedFileLoading()
    {
        $finder = $this->getFinder();
        $finder->addNamespace('foo', [$this->getPath() . '/foo', $this->getPath() . '/bar']);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo/bar/baz.php')
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo/bar/baz.php')
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/bar/bar/baz.php')
            ->andReturn(true);

        $this->assertEquals($this->getPath() . '/bar/bar/baz.php', $finder->find('foo::bar.baz'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionThrownWhenViewNotFound()
    {
        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo.php')->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getPath() . '/foo.php')
            ->andReturn(false);
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

    protected function getPath()
    {
        return __DIR__ .  '/Fixture';
    }

    protected function getFinder()
    {
        return new ViewFinder(Mock::mock(Filesystem::class), [__DIR__]);
    }
}
