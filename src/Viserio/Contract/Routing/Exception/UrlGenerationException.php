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

namespace Viserio\Contract\Routing\Exception;

use InvalidArgumentException;
use Viserio\Contract\Routing\Route as RouteContract;

class UrlGenerationException extends InvalidArgumentException implements Exception
{
    /**
     * Create a new exception for missing route parameters.
     *
     * @param \Viserio\Contract\Routing\Route $route
     */
    public function __construct(RouteContract $route)
    {
        parent::__construct(
            \sprintf(
                'Missing required parameters for [Route: %s] [URI: %s].',
                $route->getName(),
                $route->getUri()
            )
        );
    }
}
