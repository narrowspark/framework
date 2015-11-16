<?php
namespace Viserio\Database\Connectors;

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

use Viserio\Contracts\Database\Connector as ConnectorContract;

/**
 * OracleConnector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0-dev
 */
class OdbcConnector extends Connectors implements ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \PDO
     */
    public function connect(array $config)
    {
        $options = $this->getOptions($config);
        $dsn = $this->getDsn($config);

        return $this->createConnection($dsn, $config, $options);
    }


    /**
     * Get the DSN string for a DbLib connection.
     *
     * @param array $config
     *
     * @return string
     */
    protected function getDsn(array $config)
    {
        $arguments = $config['dsn'];
        $arguments['Driver'] = '{'.$arguments['Driver'].'}';

        $options = array_map(function ($key) use ($arguments) {
            return sprintf('%s=%s', $key, $arguments[$key]);
        }, array_keys($arguments));

        return 'odbc:'.implode(';', $options);
    }
}
