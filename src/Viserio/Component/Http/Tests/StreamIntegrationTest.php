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

namespace Viserio\Component\Http\Tests;

use Http\Psr7Test\StreamIntegrationTest as Psr7TestStreamIntegrationTest;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class StreamIntegrationTest extends Psr7TestStreamIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    public function createStream($data)
    {
        if ($data instanceof StreamInterface) {
            return $data;
        }

        return new Stream($data);
    }
}
