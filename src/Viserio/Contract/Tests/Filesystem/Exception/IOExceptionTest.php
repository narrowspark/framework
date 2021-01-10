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

namespace Viserio\Contract\Tests\Filesystem\Exception;

use PHPUnit\Framework\TestCase;
use Viserio\Contract\Filesystem\Exception\IOException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class IOExceptionTest extends TestCase
{
    public function testGetPath(): void
    {
        $e = new IOException('', 0, null, '/foo');

        self::assertEquals('/foo', $e->getPath(), 'The pass should be returned.');
    }
}
