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

namespace Viserio\Component\HttpFactory;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\Http\Stream\CachingStream;
use Viserio\Component\Http\Stream\LazyOpenStream;
use Viserio\Contract\Http\Exception\InvalidArgumentException;

class ServerRequestFactory implements ServerRequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createServerRequest(string $method, $uri, array $serverParams = []): ServerRequestInterface
    {
        if ($method === '') {
            if (! empty($serverParams['REQUEST_METHOD'])) {
                $method = $serverParams['REQUEST_METHOD'];
            } else {
                throw new InvalidArgumentException('Cannot determine HTTP method.');
            }
        }

        return new ServerRequest(
            $uri,
            $method,
            [],
            new CachingStream(new LazyOpenStream('php://input', 'r+')),
            '1.1',
            $serverParams
        );
    }
}
