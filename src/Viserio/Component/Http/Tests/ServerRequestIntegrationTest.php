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

namespace Viserio\Component\Http\Tests;

use Http\Psr7Test\ServerRequestIntegrationTest as Psr7TestServerRequestIntegrationTest;
use Viserio\Component\Http\ServerRequest;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ServerRequestIntegrationTest extends Psr7TestServerRequestIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        return new ServerRequest('/', 'GET', [], null, '1.1', $_SERVER);
    }
}
