<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing\Exceptions;

use Exception;

class InvalidRouteDataException extends Exception
{
    public function __construct($data)
    {
        parent::__construct(
            sprintf(
                'The supplied route data is invalid: expecting object or array, %s given',
                gettype($data)
            )
        );
    }
}
