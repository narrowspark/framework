<?php
namespace Viserio\Http;

use Psr\Http\Message\RequestInterface;
use Viserio\Contracts\Http\RequestFactory as RequestFactoryContract;

final class RequestFactory implements RequestFactoryContract
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function createRequest(
        string $method,
        $uri
    ): RequestInterface {
        return new Request(
            $uri,
            $method
        );
    }
}
