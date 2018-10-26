<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\WebServer\RequestContextProvider;

/**
 * @internal
 */
final class RequestContextProviderTest extends TestCase
{
    public function testGetContext(): void
    {
        $currentRequest = new ServerRequest('/');

        $this->assertSame(
            [
                'uri'        => (string) $currentRequest->getUri(),
                'method'     => $currentRequest->getMethod(),
                'identifier' => \spl_object_hash($currentRequest),
            ],
            (new RequestContextProvider($currentRequest))->getContext()
        );
    }
}
