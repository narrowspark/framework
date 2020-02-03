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

use Http\Psr7Test\RequestIntegrationTest as Psr7TestRequestIntegrationTest;
use Viserio\Component\Http\Request;

/**
 * @internal
 *
 * @small
 */
final class RequestIntegrationTest extends Psr7TestRequestIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        return new Request('/', 'GET');
    }
}
