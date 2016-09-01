<?php
declare(strict_types=1);
namespace Viserio\HttpFactory;

use Interop\Http\Factory\RequestFactoryInterface;
use Viserio\Http\Request;

final class RequestFactory implements RequestFactoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function createRequest($method, $uri)
    {
        return new Request(
            $uri,
            $method
        );
    }
}
