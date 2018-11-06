<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Tests\File;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\Http\Exception\FileNotFoundException;
use Viserio\Component\Http\File\File;

/**
 * @internal
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

        $this->assertSame('text/plain', $file->getMimeType());
    }
}
