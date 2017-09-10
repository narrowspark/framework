<?php
declare(strict_types=1);
namespace Viserio\Provider\Twig\Tests;

use InvalidArgumentException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contract\Filesystem\Exception\FileNotFoundException;
use Viserio\Component\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contract\View\Finder as FinderContract;
use Viserio\Provider\Twig\Loader;

class LoaderTest extends MockeryTestCase
{
    public function testExists(): void
    {
        $file = $this->mock(FilesystemContract::class);
        $file->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(true);
        $finder = $this->mock(FinderContract::class);
        $finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($file);

        $loader = new Loader($finder);

        self::assertTrue($loader->exists('test.twig'));

        $file = $this->mock(FilesystemContract::class);
        $file->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(false);
        $file->shouldReceive('getExtension')
            ->once()
            ->with('test.twig')
            ->andReturn('twig');
        $finder = $this->mock(FinderContract::class);
        $finder->shouldReceive('find')
            ->once()
            ->with('test')
            ->andThrow(new InvalidArgumentException());
        $finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($file);

        $loader = new Loader($finder);

        self::assertFalse($loader->exists('test.twig'));
    }

    public function testGetSourceContext(): void
    {
        $file = $this->mock(FilesystemContract::class);
        $file->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(true);
        $file->shouldReceive('read')
            ->once()
            ->with('test.twig')
            ->andReturn('test');
        $finder = $this->mock(FinderContract::class);
        $finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($file);

        $loader = new Loader($finder);
        $source = $loader->getSourceContext('test.twig');

        self::assertSame('test.twig', $source->getName());
        self::assertSame('test', $source->getCode());
        self::assertSame('test.twig', $source->getPath());
    }

    /**
     * @expectedException \Twig_Error_Loader
     * @expectedExceptionMessage Twig file [test.twig] was not found.
     */
    public function testGetSourceContextFileNotFound(): void
    {
        $file = $this->mock(FilesystemContract::class);
        $file->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(false);
        $file->shouldReceive('read')
            ->once()
            ->with('test.twig')
            ->andThrow(new FileNotFoundException('test.twig'));
        $file->shouldReceive('getExtension')
            ->once()
            ->with('test.twig')
            ->andReturn('twig');
        $finder = $this->mock(FinderContract::class);
        $finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($file);
        $finder->shouldReceive('find')
            ->once()
            ->andReturn(['path' => 'test.twig']);

        $loader = new Loader($finder);

        $loader->getSourceContext('test.twig');
    }

    public function testIsFresh(): void
    {
        $path = __DIR__ . '/Fixtures/twightml.twig.html';
        $date = \date('F d Y H:i:s', \filemtime($path));
        $file = $this->mock(FilesystemContract::class);
        $file->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn($path);
        $file->shouldReceive('getTimestamp')
            ->once()
            ->with($path)
            ->andReturn($date);
        $finder = $this->mock(FinderContract::class);
        $finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($file);

        $loader = new Loader($finder);

        self::assertTrue($loader->isFresh($path, $date));
    }

    public function testFindTemplate(): void
    {
        $file = $this->mock(FilesystemContract::class);
        $file->shouldReceive('has')
            ->twice()
            ->with('test.twig')
            ->andReturn(false);
        $file->shouldReceive('getExtension')
            ->twice()
            ->with('test.twig')
            ->andReturn('twig');
        $finder = $this->mock(FinderContract::class);
        $finder->shouldReceive('find')
            ->once()
            ->with('test')
            ->andReturn(['path' => 'test.twig']);
        $finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($file);

        $loader = new Loader($finder);

        self::assertSame('test.twig', $loader->findTemplate('test.twig'));

        // cache call
        self::assertSame('test.twig', $loader->findTemplate('test.twig'));
    }
}
