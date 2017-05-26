<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Tests\Filesystem\Exceptions;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contracts\Filesystem\Exceptions\IOException;

class IOExceptionTest extends TestCase
{
    public function testGetPath()
    {
        $e = new IOException('', 0, null, '/foo');

        self::assertEquals('/foo', $e->getPath(), 'The pass should be returned.');
    }
}
