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

namespace Viserio\Component\Http\Tests\File;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\File\File;
use Viserio\Contract\Http\Exception\FileNotFoundException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class FileTest extends TestCase
{
    public function testThrowsExceptionOnNotFoundFile(): void
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage('The file [] does not exist.');

        new File('');
    }

    public function testGetMimeType(): void
    {
        $file = new File(\dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'php-input-stream.txt');

        self::assertSame('text/plain', $file->getMimeType());
    }
}
