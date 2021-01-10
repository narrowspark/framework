<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Filesystem\Tests\Watcher\Resource;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent;
use Viserio\Component\Filesystem\Watcher\Resource\FileResource;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class FileResourceTest extends TestCase
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

    public function testFileChanges(): void
    {
        $this->root->addChild(new vfsStreamDirectory('testFileChanges'));

        $dir = $this->root->getChild('testFileChanges');

        $file = vfsStream::newFile('copy.txt')
            ->at($dir);

        $resource = new FileResource($file->url());

        self::assertSame([], $resource->detectChanges());

        \touch($file->url(), \time() + 1);

        self::assertEquals([new FileChangeEvent($file->url(), FileChangeEvent::FILE_CHANGED)], $resource->detectChanges());
        self::assertSame([], $resource->detectChanges());
    }
}
