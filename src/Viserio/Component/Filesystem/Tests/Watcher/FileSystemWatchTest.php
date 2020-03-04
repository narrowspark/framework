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

namespace Viserio\Component\Filesystem\Tests\Watcher;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Viserio\Component\Filesystem\Tests\Fixture\ChangeFileResource;
use Viserio\Component\Filesystem\Watcher\Event\FileChangeEvent;
use Viserio\Component\Filesystem\Watcher\FileChangeWatcher;
use Viserio\Contract\Filesystem\Watcher\Resource as ResourceContract;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class FileSystemWatchTest extends TestCase
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

    public function testWatch(): void
    {
        $this->root->addChild(new vfsStreamDirectory('temp'));

        $workspace = $this->root->getChild('temp')->url();

        $locator = new class() {
            /** @var string */
            public static $workspace;

            public static function locate(): ?ResourceContract
            {
                return new ChangeFileResource(self::$workspace . '/foobar.txt');
            }
        };

        $locator::$workspace = $workspace;

        $watcher = new FileChangeWatcher();

        $ref = new ReflectionProperty($watcher, 'locator');
        $ref->setAccessible(true);
        $ref->setValue($watcher, $locator);

        $count = 0;

        $watcher->watch($workspace, function (string $file, int $code) use (&$count, $workspace): bool {
            Assert::assertSame($workspace . '/foobar.txt', $file);
            Assert::assertSame(FileChangeEvent::FILE_CHANGED, $code);

            $count++;

            if ($count === 2) {
                return false;
            }

            return true;
        });

        self::assertSame(2, $count);
    }
}
