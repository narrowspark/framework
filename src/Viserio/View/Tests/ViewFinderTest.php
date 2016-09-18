<?php
declare(strict_types=1);
namespace Viserio\View\Tests;

use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Filesystem\Filesystem;
use Viserio\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\View\ViewFinder;

class ViewFinderTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    public function testBasicViewFinding()
    {
        $path = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.php');

        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $finder->find('foo')['path']
        );
        $this->assertEquals(
            $path,
            $finder->find('foo')['path']
        );
    }

    public function testCascadingFileLoading()
    {
        $path = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.phtml');
        $path2 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.php');

        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path2)
            ->andReturn(false);

        $this->assertEquals(
            $path,
            $finder->find('foo')['path']
        );
    }

    public function testDirectoryCascadingFileLoading()
    {
        $path = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.php');
        $path2 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'Nested/foo.php');
        $path3 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.phtml');

        $finder = $this->getFinder();
        $finder->addLocation($this->getPath() . '/' . 'Nested');
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path3)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path2)
            ->andReturn(true);

        $this->assertEquals(
            $path2,
            $finder->find('foo')['path']
        );
    }

    public function testNamespacedBasicFileLoading()
    {
        $path = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.php');

        $finder = $this->getFinder();
        $finder->addNamespace(
            'foo',
            self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo')
        );
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $finder->find('foo::bar.baz')['path']
        );
    }

    public function testCascadingNamespacedFileLoading()
    {
        $path = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.php');

        $finder = $this->getFinder();
        $finder->addNamespace(
            'foo',
            self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo')
        );
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $finder->find('foo::bar.baz')['path']
        );
        $this->assertEquals(
            self::normalizeDirectorySeparator('bar\baz.php'),
            self::normalizeDirectorySeparator($finder->find('foo::bar.baz')['name'])
        );
    }

    public function testDirectoryCascadingNamespacedFileLoading()
    {
        $path = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.php');
        $path2 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'bar/bar/baz.php');
        $path3 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.phtml');

        $finder = $this->getFinder();
        $finder->addNamespace(
            'foo',
            [
                self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo'),
                self::normalizeDirectorySeparator($this->getPath() . '/' . 'bar'),
            ]
        );
        $finder->addNamespace(
            'foo',
            self::normalizeDirectorySeparator($this->getPath() . '/' . 'baz')
        );
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path3)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path2)
            ->andReturn(true);

        $this->assertEquals(
            $path2,
            $finder->find('foo::bar.baz')['path']
        );
    }

    public function testSetAndGetPaths()
    {
        $finder = $this->getFinder();
        $finder->setPaths(['test', 'foo']);

        $this->assertCount(2, $finder->getPaths());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage View [foo] not found.
     */
    public function testExceptionThrownWhenViewNotFound()
    {
        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.php'))
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.phtml'))
            ->andReturn(false);
        $finder->find('foo');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage View [foo::foo::] has an invalid name.
     */
    public function testExceptionThrownWhenViewHasAInvalidName()
    {
        $path = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.php');

        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $finder->find('foo')['path']
        );

        $finder->find('foo::foo::');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No hint path defined for [name].
     */
    public function testExceptionThrownOnInvalidViewName()
    {
        $finder = $this->getFinder();
        $finder->find('name::');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No hint path defined for [name].
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

    public function testPrependNamespace()
    {
        $finder = $this->getFinder();
        $finder->prependNamespace('test', 'foo');
        $finder->prependNamespace('testb', 'baz');
        $finder->prependNamespace('test', 'baa');

        $this->assertCount(2, $finder->getHints());
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
        return self::normalizeDirectorySeparator(__DIR__ . '/' . 'Fixture');
    }

    protected function getFinder()
    {
        return new ViewFinder($this->mock(Filesystem::class), [$this->getPath()], ['php', 'phtml']);
    }
}
