<?php
namespace Viserio\Contracts\Routing;

/**
 * RouteCollector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
interface RouteCollector
{
    /**
     * Returns the collected route data.
     *
     * @return array
     */
    public function getData();
}
