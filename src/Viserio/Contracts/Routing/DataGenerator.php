<?php
namespace Viserio\Contracts\Routing;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

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
