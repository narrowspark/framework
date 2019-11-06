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

namespace Viserio\Component\Http\Tests\File;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\File\File;
use Viserio\Contract\Http\Exception\FileNotFoundException;

/**
 * @internal
 *
 * @small
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
