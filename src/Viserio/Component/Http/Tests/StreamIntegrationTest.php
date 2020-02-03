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

namespace Viserio\Component\Http\Tests;

use Http\Psr7Test\StreamIntegrationTest as Psr7TestStreamIntegrationTest;
use Psr\Http\Message\StreamInterface;
use Viserio\Component\Http\Stream;

/**
 * @internal
 *
 * @small
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
