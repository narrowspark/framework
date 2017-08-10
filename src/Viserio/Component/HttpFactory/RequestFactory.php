<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFactory;

use Interop\Http\Factory\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Viserio\Component\Http\Request;

final class RequestFactory implements RequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createRequest($method, $uri): RequestInterface
    {
        return new Request($uri, $method);
    }
}
