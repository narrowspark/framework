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
