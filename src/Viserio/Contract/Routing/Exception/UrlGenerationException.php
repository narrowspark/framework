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

namespace Viserio\Contract\Routing\Exception;

use InvalidArgumentException;
use Viserio\Contract\Routing\Route as RouteContract;

class UrlGenerationException extends InvalidArgumentException implements Exception
{
    /**
     * Create a new exception for missing route parameters.
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
