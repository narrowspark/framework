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

namespace Viserio\Component\Filesystem\Tests\Watcher\Resource;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent;
use Viserio\Component\Filesystem\Watcher\Resource\ArrayResource;
use Viserio\Component\Filesystem\Watcher\Resource\FileResource;

/**
 * @internal
 *
 * @small
 */
final class ArrayResourceTest extends TestCase
{
    /** @var \org\bovigo\vfs\vfsStreamDirectory */
    private $root;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->root = vfsStream::setup();
    }

    public function testFileChange(): void
    {
        $file = $this->root->url() . '/foo.txt';

        \touch($file);

        $resource = new ArrayResource([new FileResource($file)]);

        self::assertSame([], $resource->detectChanges());

        \touch($file, \time() + 1);

        self::assertEquals([new FileChangeEvent($file, FileChangeEvent::FILE_CHANGED)], $resource->detectChanges());
        self::assertSame([], $resource->detectChanges());
    }
}
