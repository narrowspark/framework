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

namespace Viserio\Component\WebServer\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Http\ServerRequest;
use Viserio\Component\WebServer\RequestContextProvider;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
