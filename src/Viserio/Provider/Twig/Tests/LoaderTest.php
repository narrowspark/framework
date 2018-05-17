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
    /**
     * @var \Mockery\MockInterface|\Viserio\Component\Contract\Filesystem\Filesystem
     */
    private $file;

    /**
     * @var \Mockery\MockInterface|\Viserio\Component\View\ViewFinder
     */
    private $finder;

    public function setUp(): void
    {
        parent::setUp();

        $this->file   = $this->mock(FilesystemContract::class);
        $this->finder = $this->mock(FinderContract::class);
    }

    public function testExists(): void
    {
        $this->file->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(true);

        $this->finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($this->file);

        $loader = new Loader($this->finder);

        self::assertTrue($loader->exists('test.twig'));

        $this->file->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(false);
        $this->file->shouldReceive('getExtension')
            ->once()
            ->with('test.twig')
            ->andReturn('twig');

        $this->finder->shouldReceive('find')
            ->once()
            ->with('test')
            ->andThrow(new InvalidArgumentException());
        $this->finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($this->file);

        $loader = new Loader($this->finder);

        self::assertFalse($loader->exists('test.twig'));
    }

    public function testGetSourceContext(): void
    {
        $this->file->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(true);
        $this->file->shouldReceive('read')
            ->once()
            ->with('test.twig')
            ->andReturn('test');

        $this->finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($this->file);

        $loader = new Loader($this->finder);
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
        $this->file->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(false);
        $this->file->shouldReceive('read')
            ->once()
            ->with('test.twig')
            ->andThrow(new FileNotFoundException('test.twig'));
        $this->file->shouldReceive('getExtension')
            ->once()
            ->with('test.twig')
            ->andReturn('twig');

        $this->finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($this->file);
        $this->finder->shouldReceive('find')
            ->once()
            ->andReturn(['path' => 'test.twig']);

        $loader = new Loader($this->finder);

        $loader->getSourceContext('test.twig');
    }

    public function testIsFresh(): void
    {
        $path = __DIR__ . '/Fixture/twightml.twig.html';
        $date = \date('F d Y H:i:s', \filemtime($path));

        $this->file->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn($path);
        $this->file->shouldReceive('getTimestamp')
            ->once()
            ->with($path)
            ->andReturn($date);

        $this->finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($this->file);

        $loader = new Loader($this->finder);

        self::assertTrue($loader->isFresh($path, $date));
    }

    public function testFindTemplate(): void
    {
        $this->file->shouldReceive('has')
            ->twice()
            ->with('test.twig')
            ->andReturn(false);
        $this->file->shouldReceive('getExtension')
            ->twice()
            ->with('test.twig')
            ->andReturn('twig');

        $this->finder->shouldReceive('find')
            ->once()
            ->with('test')
            ->andReturn(['path' => 'test.twig']);
        $this->finder->shouldReceive('getFilesystem')
            ->once()
            ->andReturn($this->file);

        $loader = new Loader($this->finder);

        self::assertSame('test.twig', $loader->findTemplate('test.twig'));

        // cache call
        self::assertSame('test.twig', $loader->findTemplate('test.twig'));
    }
}
