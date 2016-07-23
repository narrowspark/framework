<?php

declare(strict_types=1);
namespace Viserio\Contracts\Routing;

interface RouteStrategy
{
    /**
     * Types of route strategies.
     */
    const REQUEST_RESPONSE_STRATEGY = 0;
    const RESTFUL_STRATEGY = 1;
    const URI_STRATEGY = 2;
}
