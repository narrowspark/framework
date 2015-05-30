<?php

namespace Brainwave\Filesystem\Adapters;

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
 * ConnectionFactory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3-dev
 */
class ConnectionFactory
{
    protected $defaultDriver = [
        'awss3' => 'AwsS3',
        'ftp' => 'Ftp',
        'local' => 'Local',
        'null' => 'Null',
        'rackspace' => 'Rackspace',
        'sftp' => 'Sftp',
        'zip' => 'Zip',
    ];

    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return \League\Flysystem\AdapterInterface
     */
    public function make(array $config)
    {
        $connector = $this->createConnector($config);

        $adapter = new $connector();

        return $adapter->connect($config);
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string
     */
    public function createConnector(array $config)
    {
        if (!isset($config['driver'])) {
            throw new \InvalidArgumentException('A driver must be specified.');
        }

        if (isset($this->defaultDriver[$config['driver']])) {
            return $this->defaultDriver[$config['driver']]().'Connector';
        }

        throw new \InvalidArgumentException(sprintf('Unsupported driver [%s]', $config['driver']));
    }
}
