<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Tests\Filesystem\Exception;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Filesystem\Exception\IOException;

class IOExceptionTest extends TestCase
{
    public function testGetPath(): void
    {
        $e = new IOException('', 0, null, '/foo');

        self::assertEquals('/foo', $e->getPath(), 'The pass should be returned.');
    }
}
