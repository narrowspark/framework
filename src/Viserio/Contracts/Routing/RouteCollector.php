<?php
declare(strict_types=1);
namespace Viserio\Contracts\Routing;

interface RouteCollector
{
    /**
     * Returns the collected route data.
     *
     * @return array
     */
    public function getData();
}
