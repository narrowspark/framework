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
 * Connector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
 */
interface Connector
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config);
}
