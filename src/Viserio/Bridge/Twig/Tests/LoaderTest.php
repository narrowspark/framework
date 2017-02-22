<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Tests;

use InvalidArgumentException;
use Mockery as Mock;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Bridge\Twig\Loader;
use Viserio\Component\Contracts\Filesystem\Exception\FileNotFoundException;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;
use Viserio\Component\Contracts\View\Finder as FinderContract;

class LoaderTest extends MockeryTestCase
{
    public function testExists()
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

    public function testGetSourceContext()
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
     * @expectedExceptionMessage Twig file "test.twig" was not found.
     */
    public function testGetSourceContextFileNotFound()
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

    public function testIsFresh()
    {
        $path = __DIR__ . '/Fixtures/twightml.twig.html';
        $date = date('F d Y H:i:s', filemtime($path));
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

    public function testFindTemplate()
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
