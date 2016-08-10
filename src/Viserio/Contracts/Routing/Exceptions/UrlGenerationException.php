<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing\Exceptions;

use Exception;

class UrlGenerationException extends Exception
{
    /**
     * Create a new exception for missing route parameters.
     *
     * @param \Viserio\Routing\Route $route
     *
     * @return static
     */
    public static function forMissingParameters($route)
    {
        return new static(sprintf(
            'Missing required parameters for [Route: %s] [URI: %s].',
            $route->getName(),
            $route->getPath()
        ));
    }
}
