<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Routing\Exception;

use InvalidArgumentException;
use Viserio\Component\Contract\Routing\Route as RouteContract;

class UrlGenerationException extends InvalidArgumentException implements Exception
{
    /**
     * Create a new exception for missing route parameters.
     *
     * @param \Viserio\Component\Contract\Routing\Route $route
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
