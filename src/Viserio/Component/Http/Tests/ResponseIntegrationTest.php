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

use Http\Psr7Test\ResponseIntegrationTest as Psr7TestResponseIntegrationTest;
use Viserio\Component\Http\Response;

/**
 * @internal
 *
 * @small
 */
final class ResponseIntegrationTest extends Psr7TestResponseIntegrationTest
{
    /**
     * {@inheritdoc}
     */
    public function createSubject()
    {
        return new Response();
    }
}
