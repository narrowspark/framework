<?php
namespace Viserio\Contracts\Cache;

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
 * @version     0.10.0-dev
 */

/**
 * Factory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Factory
{
    /**
     * Builder.
     *
     * @param string $driver  The cache driver to use
     * @param array  $options
     *
     * @return mixed
     */
    public function driver($driver, array $options = []);
}
