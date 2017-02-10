<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests;

use Mockery as Mock;
use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Filesystem\Filesystem;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\View\ViewFinder;

class ViewFinderTest extends TestCase
{
    use MockeryTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

    public function testBasicViewFinding()
    {
        $path = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.php');

        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        self::assertEquals(
            $path,
            $finder->find('foo')['path']
        );
        self::assertEquals(
            $path,
            $finder->find('foo')['path']
        );
    }

    public function testCascadingFileLoading()
    {
        $path  = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.phtml');
        $path2 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.php');

        $finder = $this->getFinder();
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path2)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        self::assertEquals(
            $path,
            $finder->find('foo')['path']
        );
    }

    public function testDirectoryCascadingFileLoading()
    {
        $path  = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.php');
        $path2 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'Nested/foo.php');
        $path3 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.phtml');
        $path4 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.css');

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
            ->with($path4)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path2)
            ->andReturn(true);

        self::assertEquals(
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

        self::assertEquals(
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

        self::assertEquals(
            $path,
            $finder->find('foo::bar.baz')['path']
        );
        self::assertEquals(
            self::normalizeDirectorySeparator('bar\baz.php'),
            self::normalizeDirectorySeparator($finder->find('foo::bar.baz')['name'])
        );
    }

    public function testDirectoryCascadingNamespacedFileLoading()
    {
        $path  = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.php');
        $path2 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'bar/bar/baz.php');
        $path3 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.phtml');
        $path4 = self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo/bar/baz.css');

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
            ->with($path4)
            ->andReturn(false);
        $finder->getFilesystem()
            ->shouldReceive('has')
            ->once()
            ->with($path2)
            ->andReturn(true);

        self::assertEquals(
            $path2,
            $finder->find('foo::bar.baz')['path']
        );
    }

    public function testSetAndGetPaths()
    {
        $finder = $this->getFinder();
        $finder->setPaths(['test', 'foo']);

        self::assertCount(2, $finder->getPaths());
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
            ->with(self::normalizeDirectorySeparator($this->getPath() . '/' . 'foo.css'))
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

        self::assertEquals(
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

        self::assertEquals('baz', reset($extensions));
    }

    public function testAddingExtensionsReplacesOldOnes()
    {
        $finder = $this->getFinder();
        $finder->addExtension('baz');
        $finder->addExtension('baz');

        self::assertCount(4, $finder->getExtensions());
    }

    public function testPrependNamespace()
    {
        $finder = $this->getFinder();
        $finder->prependNamespace('test', 'foo');
        $finder->prependNamespace('testb', 'baz');
        $finder->prependNamespace('test', 'baa');

        self::assertCount(2, $finder->getHints());
    }

    public function testPassingViewWithHintReturnsTrue()
    {
        $finder = $this->getFinder();

        self::assertTrue($finder->hasHintInformation('hint::foo.bar'));
    }

    public function testPassingViewWithoutHintReturnsFalse()
    {
        $finder = $this->getFinder();

        self::assertFalse($finder->hasHintInformation('foo.bar'));
    }

    public function testPassingViewWithFalseHintReturnsFalse()
    {
        $finder = $this->getFinder();

        self::assertFalse($finder->hasHintInformation('::foo.bar'));
    }

    public function testPrependLocation()
    {
        $finder = $this->getFinder();
        $finder->prependLocation('test');

        self::assertSame(['test', $this->getPath()], $finder->getPaths());
    }

    protected function getPath()
    {
        return self::normalizeDirectorySeparator(__DIR__ . '/' . 'Fixture');
    }

    protected function getFinder()
    {
        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('viserio')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('viserio')
            ->andReturn([
                'view' => [
                    'paths'      => [$this->getPath()],
                    'extensions' => ['php', 'phtml', 'css'],
                ],
            ]);

        return new ViewFinder(
            $this->mock(Filesystem::class),
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );
    }
}
