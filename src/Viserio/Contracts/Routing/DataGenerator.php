<?php
namespace Viserio\Contracts\Routing;

/**
 * DataGenerator.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.5
 */
interface DataGenerator
{
    /**
     * Get formatted route data for use by a URL generator.
     *
     * @return array
     */
    public function getData();
}
