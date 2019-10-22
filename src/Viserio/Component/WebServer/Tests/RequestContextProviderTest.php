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

namespace Viserio\Component\WebServer\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\WebServer\RequestContextProvider;

/**
 * @internal
 *
 * @small
 */
final class RequestContextProviderTest extends TestCase
{
    public function testGetContext(): void
    {
        $currentRequest = new ServerRequest('/');

        self::assertSame(
            [
                'uri' => (string) $currentRequest->getUri(),
                'method' => $currentRequest->getMethod(),
                'identifier' => \spl_object_hash($currentRequest),
            ],
            (new RequestContextProvider($currentRequest))->getContext()
        );
    }
}
