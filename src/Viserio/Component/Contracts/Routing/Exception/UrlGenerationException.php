<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Routing\Exception;

use InvalidArgumentException;
use Viserio\Component\Contracts\Routing\Route as RouteContract;

class UrlGenerationException extends InvalidArgumentException implements Exception
{
    /**
     * Create a new exception for missing route parameters.
     *
     * @param \Viserio\Component\Contracts\Routing\Route $route
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
