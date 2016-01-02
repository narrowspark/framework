<?php
namespace Viserio\View\Test;

use Mockery as Mock;
use Viserio\View\ViewFinder;
use Viserio\Filesystem\Filesystem;
use Viserio\Support\Traits\DirectorySeparatorTrait;

class ViewFinderTest extends \PHPUnit_Framework_TestCase
{
    use DirectorySeparatorTrait;

    public function tearDown()
    {
        Mock::close();
    }

    public function testBasicViewFinding()
    {
        $path = $this->getDirectorySeparator($this->getPath() . '/' . 'foo.php');

        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $finder->find('foo')
        );
    }

    public function testCascadingFileLoading()
    {
        $path = $this->getDirectorySeparator($this->getPath() . '/' . 'foo.phtml');
        $path2 = $this->getDirectorySeparator($this->getPath() . '/' . 'foo.php');

        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturn(true);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path2)
            ->andReturn(false);

        $this->assertEquals(
            $path,
            $finder->find('foo')
        );
    }

    public function testDirectoryCascadingFileLoading()
    {
        $path  = $this->getDirectorySeparator($this->getPath() . '/' . 'foo.php');
        $path2 = $this->getDirectorySeparator($this->getPath() . '/' . 'Nested/foo.php');
        $path3 = $this->getDirectorySeparator($this->getPath() . '/' . 'foo.phtml');

        $finder = $this->getFinder();
        $finder->addLocation($this->getPath() . '/' . 'Nested');
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path3)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path2)
            ->andReturn(true);

        $this->assertEquals(
            $path2,
            $finder->find('foo')
        );
    }

    public function testNamespacedBasicFileLoading()
    {
        $path = $this->getDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.php');

        $finder = $this->getFinder();
        $finder->addNamespace(
            'foo',
            $this->getDirectorySeparator($this->getPath() . '/' . 'foo')
        );
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $finder->find('foo::bar.baz')
        );
    }

    public function testCascadingNamespacedFileLoading()
    {
        $path  = $this->getDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.php');

        $finder = $this->getFinder();
        $finder->addNamespace(
            'foo',
            $this->getDirectorySeparator($this->getPath() . '/' . 'foo')
        );
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $finder->find('foo::bar.baz')
        );
    }

    public function testDirectoryCascadingNamespacedFileLoading()
    {
        $path  = $this->getDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.php');
        $path2 = $this->getDirectorySeparator($this->getPath() . '/' . 'bar/bar/baz.php');
        $path3 = $this->getDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.phtml');

        $finder = $this->getFinder();
        $finder->addNamespace(
            'foo',
            [
                $this->getPath() . '/' . 'foo',
                $this->getPath() . '/' . 'bar'
            ]
        );
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path3)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path2)
            ->andReturn(true);

        $this->assertEquals(
            $path2,
            $finder->find('foo::bar.baz')
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionThrownWhenViewNotFound()
    {
        $path = $this->getDirectorySeparator($this->getPath() . '/' . 'foo.php');

        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($path)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('exists')
            ->once()
            ->with($this->getDirectorySeparator($this->getPath() . '/' . 'foo.phtml'))
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
        return $this->getDirectorySeparator(__DIR__ . '/' . 'Fixture');
    }

    protected function getFinder()
    {
        return new ViewFinder(Mock::mock(Filesystem::class), [$this->getPath()]);
    }
}
