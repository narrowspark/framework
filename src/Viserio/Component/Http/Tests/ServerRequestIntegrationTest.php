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

namespace Viserio\Component\Http\Tests;

use Http\Psr7Test\ServerRequestIntegrationTest as Psr7TestServerRequestIntegrationTest;
use Viserio\Component\Http\ServerRequest;

/**
 * @internal
 *
 * @small
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
