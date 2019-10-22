<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Provider\Twig\Tests;

use InvalidArgumentException;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig_Error_Loader;
use Viserio\Contract\Filesystem\Exception\FileNotFoundException;
use Viserio\Contract\Filesystem\Filesystem as FilesystemContract;
use Viserio\Contract\View\Finder as FinderContract;
use Viserio\Provider\Twig\Loader;

/**
 * @internal
 *
 * @small
 */
final class LoaderTest extends MockeryTestCase
{
    /** @var \Mockery\MockInterface|\Viserio\Contract\Filesystem\Filesystem */
    private $filesystem;

    /** @var \Mockery\MockInterface|\Viserio\Component\View\ViewFinder */
    private $finder;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = \Mockery::mock(FilesystemContract::class);
        $this->finder = \Mockery::mock(FinderContract::class);
    }

    public function testExists(): void
    {
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(true);

        $loader = new Loader($this->finder, $this->filesystem);

        self::assertTrue($loader->exists('test.twig'));

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(false);
        $this->filesystem->shouldReceive('getExtension')
            ->once()
            ->with('test.twig')
            ->andReturn('twig');

        $this->finder->shouldReceive('find')
            ->once()
            ->with('test')
            ->andThrow(new InvalidArgumentException());

        $loader = new Loader($this->finder, $this->filesystem);

        self::assertFalse($loader->exists('test.twig'));
    }

    public function testGetSourceContext(): void
    {
        $this->filesystem->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(true);
        $this->filesystem->shouldReceive('read')
            ->once()
            ->with('test.twig')
            ->andReturn('test');

        $loader = new Loader($this->finder, $this->filesystem);
        $source = $loader->getSourceContext('test.twig');

        self::assertSame('test.twig', $source->getName());
        self::assertSame('test', $source->getCode());
        self::assertSame('test.twig', $source->getPath());
    }

    public function testGetSourceContextFileNotFound(): void
    {
        $this->expectException(Twig_Error_Loader::class);
        $this->expectExceptionMessage('Twig file [test.twig] was not found.');

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(false);
        $this->filesystem->shouldReceive('read')
            ->once()
            ->with('test.twig')
            ->andThrow(new FileNotFoundException('test.twig'));
        $this->filesystem->shouldReceive('getExtension')
            ->once()
            ->with('test.twig')
            ->andReturn('twig');

        $this->finder->shouldReceive('find')
            ->once()
            ->andReturn(['path' => 'test.twig']);

        $loader = new Loader($this->finder, $this->filesystem);

        $loader->getSourceContext('test.twig');
    }

    public function testIsFresh(): void
    {
        $path = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'twightml.twig.html';
        $date = \date('F d Y H:i:s', (int) \filemtime($path));

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn($path);
        $this->filesystem->shouldReceive('getTimestamp')
            ->once()
            ->with($path)
            ->andReturn($date);

        $loader = new Loader($this->finder, $this->filesystem);

        self::assertTrue($loader->isFresh($path, $date));
    }

    public function testFindTemplate(): void
    {
        $this->filesystem->shouldReceive('has')
            ->twice()
            ->with('test.twig')
            ->andReturn(false);
        $this->filesystem->shouldReceive('getExtension')
            ->twice()
            ->with('test.twig')
            ->andReturn('twig');

        $this->finder->shouldReceive('find')
            ->once()
            ->with('test')
            ->andReturn(['path' => 'test.twig']);

        $loader = new Loader($this->finder, $this->filesystem);

        self::assertSame('test.twig', $loader->findTemplate('test.twig'));

        // cache call
        self::assertSame('test.twig', $loader->findTemplate('test.twig'));
    }
}
