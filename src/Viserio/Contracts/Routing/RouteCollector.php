<?php
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
