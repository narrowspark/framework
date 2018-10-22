<?php
declare(strict_types=1);
namespace Viserio\Component\View\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Filesystem\Filesystem;
use Viserio\Component\Contract\View\Exception\InvalidArgumentException;
use Viserio\Component\View\ViewFinder;

/**
 * @internal
 */
final class ViewFinderTest extends MockeryTestCase
{
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

        $this->path = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture';

        $this->filesystem = $this->mock(Filesystem::class);
        $this->finder     = new ViewFinder(
            $this->filesystem,
            [
                'viserio' => [
                    'view' => [
                        'paths' => [$this->path],
                    ],
                ],
            ]
        );
    }

    public function testBasicViewFinding(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo.php';

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        static::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
        // cache test
        static::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
    }

    public function testCascadingFileLoading(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo.phtml';

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.php')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        static::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
    }

    public function testDirectoryCascadingFileLoading(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'Nested' . \DIRECTORY_SEPARATOR . 'foo.php';

        $this->finder->addLocation($this->path . \DIRECTORY_SEPARATOR . 'Nested');
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.php')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.phtml')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.css')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.js')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.md')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        static::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );
    }

    public function testNamespacedBasicFileLoading(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.php';

        $this->finder->addNamespace(
            'foo',
            $this->path . \DIRECTORY_SEPARATOR . 'foo'
        );
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        static::assertEquals(
            $path,
            $this->finder->find('foo::bar.baz')['path']
        );
    }

    public function testCascadingNamespacedFileLoading(): void
    {
        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.php';

        $this->finder->addNamespace(
            'foo',
            $this->path . \DIRECTORY_SEPARATOR . 'foo'
        );
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        static::assertEquals(
            $path,
            $this->finder->find('foo::bar.baz')['path']
        );
        static::assertEquals(
            'bar' . \DIRECTORY_SEPARATOR . 'baz.php',
            $this->finder->find('foo::bar.baz')['name']
        );
    }

    public function testDirectoryCascadingNamespacedFileLoading(): void
    {
        $path  = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.php';
        $path2 = $this->path . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.php';
        $path3 = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.phtml';
        $path4 = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.css';
        $path5 = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.js';
        $path6 = $this->path . \DIRECTORY_SEPARATOR . 'foo' . \DIRECTORY_SEPARATOR . 'bar' . \DIRECTORY_SEPARATOR . 'baz.md';

        $this->finder->addNamespace(
            'foo',
            [
                $this->path . \DIRECTORY_SEPARATOR . 'foo',
                $this->path . \DIRECTORY_SEPARATOR . 'bar',
            ]
        );
        $this->finder->addNamespace(
            'foo',
            $this->path . \DIRECTORY_SEPARATOR . 'baz'
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

        static::assertEquals(
            $path2,
            $this->finder->find('foo::bar.baz')['path']
        );
    }

    public function testSetAndGetPaths(): void
    {
        $this->finder->setPaths(['test', 'foo']);

        static::assertCount(2, $this->finder->getPaths());
    }

    public function testExceptionThrownWhenViewNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('View [foo] not found.');

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.php')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.css')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.phtml')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.js')
            ->andReturn(false);
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($this->path . \DIRECTORY_SEPARATOR . 'foo.md')
            ->andReturn(false);
        $this->finder->find('foo');
    }

    public function testExceptionThrownWhenViewHasAInvalidName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('View [foo::foo::] has an invalid name.');

        $path = $this->path . \DIRECTORY_SEPARATOR . 'foo.php';

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn(true);

        static::assertEquals(
            $path,
            $this->finder->find('foo')['path']
        );

        $this->finder->find('foo::foo::');
    }

    public function testExceptionThrownOnInvalidViewName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No hint path defined for [name].');

        $this->finder->find('name::');
    }

    public function testExceptionThrownWhenNoHintPathIsRegistered(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No hint path defined for [name].');

        $this->finder->find('name::foo');
    }

    public function testAddingExtensionPrependsNotAppends(): void
    {
        $this->finder->addExtension('baz');
        $extensions = $this->finder->getExtensions();

        static::assertEquals('baz', \reset($extensions));
    }

    public function testAddingExtensionsReplacesOldOnes(): void
    {
        $this->finder->addExtension('baz');
        $this->finder->addExtension('baz');

        static::assertCount(6, $this->finder->getExtensions());
    }

    public function testPrependNamespace(): void
    {
        $this->finder->prependNamespace('test', 'foo');
        $this->finder->prependNamespace('testb', 'baz');
        $this->finder->prependNamespace('test', 'baa');

        static::assertCount(2, $this->finder->getHints());
    }

    public function testPassingViewWithHintReturnsTrue(): void
    {
        static::assertTrue($this->finder->hasHintInformation('hint::foo.bar'));
    }

    public function testPassingViewWithoutHintReturnsFalse(): void
    {
        static::assertFalse($this->finder->hasHintInformation('foo.bar'));
    }

    public function testPassingViewWithFalseHintReturnsFalse(): void
    {
        static::assertFalse($this->finder->hasHintInformation('::foo.bar'));
    }

    public function testPrependLocation(): void
    {
        $this->finder->prependLocation('test');

        static::assertSame(['test', $this->path], $this->finder->getPaths());
    }
}
