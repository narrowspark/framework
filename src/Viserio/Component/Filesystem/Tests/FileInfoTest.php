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

namespace Viserio\Component\Filesystem\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\FileInfo;
use Viserio\Component\Filesystem\Path;
use Viserio\Contract\Filesystem\Exception\NotFoundException;

/**
 * @covers \Viserio\Component\Filesystem\FileInfo
 *
 * @internal
 *
 * @small
 */
final class FileInfoTest extends TestCase
{
    public function testInvalidPath(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('File [random] could not be found.');

        new FileInfo('random');
    }

    public function testRelatives(): void
    {
        $smartFileInfo = new FileInfo(__FILE__);

        self::assertNotSame($smartFileInfo->getRelativePath(), $smartFileInfo->getRealPath());
        self::assertStringEndsWith($this->normalize($smartFileInfo->getRelativePath()), __DIR__);
        self::assertStringEndsWith($this->normalize($smartFileInfo->getRelativePathname()), __FILE__);
    }

    public function testRelativeToDir(): void
    {
        $smartFileInfo = new FileInfo(__DIR__ . '/Fixture/' . __FUNCTION__ . '.txt');

        self::assertSame('Fixture/' . __FUNCTION__ . '.txt', $smartFileInfo->getRelativeFilePathFromDirectory(__DIR__));
    }

    public function testRelativeToDirException(): void
    {
        $this->expectException(NotFoundException::class);

        $smartFileInfo = new FileInfo(__FILE__);
        $smartFileInfo->getRelativeFilePathFromDirectory('non-existing-path');
    }

    public function testGetBasenameWithoutSuffix(): void
    {
        $smartFileInfo = new FileInfo(__FILE__);

        self::assertSame('FileInfoTest', $smartFileInfo->getBasenameWithoutSuffix());
    }

    public function testGetContents(): void
    {
        $smartFileInfo = new FileInfo(__FILE__);

        self::assertStringContainsString('<?php', $smartFileInfo->getContents());
    }

    public function testEndsWith(): void
    {
        $smartFileInfo = new FileInfo(__FILE__);

        self::assertTrue($smartFileInfo->endsWith('.php'));
    }

    public function testGetFilenameWithoutExtension(): void
    {
        $smartFileInfo = new FileInfo(__FILE__);

        self::assertSame('FileInfoTest', $smartFileInfo->getFilenameWithoutExtension());
    }

    /**
     * Normalize the given path (transform each blackslash into a real directory separator).
     *
     * @param string $path
     *
     * @return string
     */
    private function normalize(string $path): string
    {
        return \str_replace('/', \DIRECTORY_SEPARATOR, $path);
    }
}
