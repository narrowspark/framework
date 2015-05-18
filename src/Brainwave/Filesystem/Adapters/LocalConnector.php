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
 * @version     0.9.8-dev
 */

use Brainwave\Contracts\Filesystem\Connector as ConnectorContract;
use Brainwave\Support\Arr;
use League\Flysystem\Adapter\Local;

/**
 * ConnectionFactory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3-dev
 */
class LocalConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return Local
     */
    public function connect(array $config)
    {
        $config = $this->getConfig($config);

        return $this->getAdapter($config);
    }

    /**
     * Get the configuration.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('path', $config)) {
            throw new \InvalidArgumentException('The local connector requires a path.');
        }

        return Arr::only($config, ['path']);
    }

    /**
     * Get the local adapter.
     *
     * @param string[] $config
     *
     * @return \League\Flysystem\Adapter\Local
     */
    protected function getAdapter(array $config)
    {
        return new Local($config['path']);
    }
}
