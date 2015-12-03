<?php
namespace Viserio\Contracts\Routing;

interface DataGenerator
{
    /**
     * Get formatted route data for use by a URL generator.
     *
     * @return array
     */
    public function getData();
}
