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

use DateTime;
use InvalidArgumentException;
use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Twig\Error\LoaderError;
use Viserio\Contract\Filesystem\Exception\NotFoundException;
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

        $this->filesystem = Mockery::mock(FilesystemContract::class);
        $this->finder = Mockery::mock(FinderContract::class);
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
        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage('Twig file [test.twig] was not found.');

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with('test.twig')
            ->andReturn(false);
        $this->filesystem->shouldReceive('read')
            ->once()
            ->with('test.twig')
            ->andThrow(new NotFoundException(NotFoundException::TYPE_FILE, 'test.twig'));
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
        $date = $this->mock(DateTime::class);
        $date->shouldReceive('getTimestamp')
            ->once()
            ->andReturn((int) \filemtime($path));

        $this->filesystem->shouldReceive('has')
            ->once()
            ->with($path)
            ->andReturn($path);
        $this->filesystem->shouldReceive('getLastModified')
            ->once()
            ->with($path)
            ->andReturn($date);

        $loader = new Loader($this->finder, $this->filesystem);

        self::assertTrue($loader->isFresh($path, (int) \filemtime($path)));
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

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
