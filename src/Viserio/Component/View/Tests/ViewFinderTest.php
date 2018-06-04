<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\Filesystem\Filesystem;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;
use Viserio\Component\View\ViewFinder;

/**
 * @internal
 */
final class ViewFinderTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Viserio\Component\View\ViewFinder
     */
    private $finder;

    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\Filesystem\Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->path = self::normalizeDirectorySeparator(__DIR__ . '/' . 'Fixture');

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
                    'paths' => [$this->path],
                ],
            ]);

        $this->filesystem = $this->mock(Filesystem::class);
        $this->finder     = new ViewFinder(
            $this->filesystem,
            new ArrayContainer([
                RepositoryContract::class => $config,
            ])
        );
    }

    public function testBasicViewFinding(): void
    {
        $path = self::normalizeDirectorySeparator($this->path . '/' . 'foo.php');

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
        // cache test
        $this->assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
    }

    public function testCascadingFileLoading(): void
    {
        $path = self::normalizeDirectorySeparator($this->path . '/' . 'foo.phtml');

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.php'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
    }

    public function testDirectoryCascadingFileLoading(): void
    {
        $path = self::normalizeDirectorySeparator($this->path . '/' . 'Nested/foo.php');

        $this->finder->addLocation($this->path . '/' . 'Nested');
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.php'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.phtml'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.css'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.js'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.md'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
    }

    public function testNamespacedBasicFileLoading(): void
    {
        $path = self::normalizeDirectorySeparator($this->path . '/' . 'foo/bar/baz.php');

        $this->finder->addNamespace(
            'foo',
            self::normalizeDirectorySeparator($this->path . '/' . 'foo')
        );
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $this->finder->find('foo::bar.baz')['path']
        );
    }

    public function testCascadingNamespacedFileLoading(): void
    {
        $path = self::normalizeDirectorySeparator($this->path . '/' . 'foo/bar/baz.php');

        $this->finder->addNamespace(
            'foo',
            self::normalizeDirectorySeparator($this->path . '/' . 'foo')
        );
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $this->finder->find('foo::bar.baz')['path']
        );
        $this->assertEquals(
            self::normalizeDirectorySeparator('bar\baz.php'),
            self::normalizeDirectorySeparator($this->finder->find('foo::bar.baz')['name'])
        );
    }

    public function testDirectoryCascadingNamespacedFileLoading(): void
    {
        $path  = self::normalizeDirectorySeparator($this->path . '/' . 'foo/bar/baz.php');
        $path2 = self::normalizeDirectorySeparator($this->path . '/' . 'bar/bar/baz.php');
        $path3 = self::normalizeDirectorySeparator($this->path . '/' . 'foo/bar/baz.phtml');
        $path4 = self::normalizeDirectorySeparator($this->path . '/' . 'foo/bar/baz.css');
        $path5 = self::normalizeDirectorySeparator($this->path . '/' . 'foo/bar/baz.js');
        $path6 = self::normalizeDirectorySeparator($this->path . '/' . 'foo/bar/baz.md');

        $this->finder->addNamespace(
            'foo',
            [
                self::normalizeDirectorySeparator($this->path . '/' . 'foo'),
                self::normalizeDirectorySeparator($this->path . '/' . 'bar'),
            ]
        );
        $this->finder->addNamespace(
            'foo',
            self::normalizeDirectorySeparator($this->path . '/' . 'baz')
        );
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path3)
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path4)
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path5)
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path6)
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path2)
            ->andReturn(true);

        $this->assertEquals(
            $path2,
            $this->finder->find('foo::bar.baz')['path']
        );
    }

    public function testSetAndGetPaths(): void
    {
        $this->finder->setPaths(['test', 'foo']);

        $this->assertCount(2, $this->finder->getPaths());
    }

    public function testExceptionThrownWhenViewNotFound(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('View [foo] not found.');

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.php'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.css'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.phtml'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.js'))
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with(self::normalizeDirectorySeparator($this->path . '/' . 'foo.md'))
            ->andReturn(false);
        $this->finder->find('foo');
    }

    public function testExceptionThrownWhenViewHasAInvalidName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('View [foo::foo::] has an invalid name.');

        $path = self::normalizeDirectorySeparator($this->path . '/' . 'foo.php');

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        $this->assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );

        $this->finder->find('foo::foo::');
    }

    public function testExceptionThrownOnInvalidViewName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No hint path defined for [name].');

        $this->finder->find('name::');
    }

    public function testExceptionThrownWhenNoHintPathIsRegistered(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No hint path defined for [name].');

        $this->finder->find('name::foo');
    }

    public function testAddingExtensionPrependsNotAppends(): void
    {
        $this->finder->addExtension('baz');
        $extensions = $this->finder->getExtensions();

        $this->assertEquals('baz', \reset($extensions));
    }

    public function testAddingExtensionsReplacesOldOnes(): void
    {
        $this->finder->addExtension('baz');
        $this->finder->addExtension('baz');

        $this->assertCount(6, $this->finder->getExtensions());
    }

    public function testPrependNamespace(): void
    {
        $this->finder->prependNamespace('test', 'foo');
        $this->finder->prependNamespace('testb', 'baz');
        $this->finder->prependNamespace('test', 'baa');

        $this->assertCount(2, $this->finder->getHints());
    }

    public function testPassingViewWithHintReturnsTrue(): void
    {
        $this->assertTrue($this->finder->hasHintInformation('hint::foo.bar'));
    }

    public function testPassingViewWithoutHintReturnsFalse(): void
    {
        $this->assertFalse($this->finder->hasHintInformation('foo.bar'));
    }

    public function testPassingViewWithFalseHintReturnsFalse(): void
    {
        $this->assertFalse($this->finder->hasHintInformation('::foo.bar'));
    }

    public function testPrependLocation(): void
    {
        $this->finder->prependLocation('test');

        $this->assertSame(['test', $this->path], $this->finder->getPaths());
    }
}
