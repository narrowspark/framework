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

namespace Viserio\Contract\Tests\Filesystem\Exception;

use PHPUnit\Framework\TestCase;
use Viserio\Contract\Filesystem\Exception\IOException;

/**
 * @internal
 *
 * @small
 */
final class IOExceptionTest extends TestCase
{
    public function testGetPath(): void
    {
        $e = new IOException('', 0, null, '/foo');

        self::assertEquals('/foo', $e->getPath(), 'The pass should be returned.');
    }
}
