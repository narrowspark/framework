<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Http\Exception\InvalidArgumentException;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\Http\Stream\LazyOpenStream;

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
            new LazyOpenStream('php://temp', 'r+'),
            '1.1',
            $serverParams
        );
    }
}
