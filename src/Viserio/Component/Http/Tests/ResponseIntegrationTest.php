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

use Http\Psr7Test\ResponseIntegrationTest as Psr7TestResponseIntegrationTest;
use Viserio\Component\Http\Response;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
