<?php
namespace Viserio\Contracts\Database;

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
 * ConnectionResolver.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface ConnectionResolver
{
    /**
     * Get a database connection instance.
     *
     * @param string|null $name
     *
     * @return \Viserio\Database\Connection
     */
    public function connection($name = null);

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection();

    /**
     * Set the default connection name.
     *
     * @param string $name
     */
    public function setDefaultConnection($name);
}
