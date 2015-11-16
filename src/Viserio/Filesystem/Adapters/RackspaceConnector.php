<?php
namespace Viserio\Filesystem\Adapters;

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

use Viserio\Contracts\Filesystem\Connector as ConnectorContract;
use Viserio\Support\Arr;
use League\Flysystem\Adapter\Rackspace;
use OpenCloud\ObjectStore\Resource\Container;
use OpenCloud\OpenStack;

/**
 * ConnectionFactory.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3-dev
 */
class RackspaceConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return object
     */
    public function connect(array $config)
    {
        $auth = $this->getAuth($config);
        $client = $this->getClient($auth);

        return $this->getAdapter($client);
    }

    /**
     * Get the authentication data.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getAuth(array $config)
    {
        if (!array_key_exists('username', $config) || !array_key_exists('password', $config)) {
            throw new \InvalidArgumentException('The rackspace connector requires authentication.');
        }

        if (!array_key_exists('endpoint', $config) || !array_key_exists('container', $config)) {
            throw new \InvalidArgumentException('The rackspace connector requires configuration.');
        }

        return Arr::only($config, ['username', 'password', 'endpoint', 'container']);
    }

    /**
     * Get the rackspace client.
     *
     * @param string[] $auth
     *
     * @return \OpenCloud\ObjectStore\Resource\Container
     */
    protected function getClient(array $auth)
    {
        $client = new OpenStack($auth['endpoint'], [
            'username' => $auth['username'],
            'password' => $auth['password'],
        ]);

        return $client->objectStoreService('cloudFiles', 'LON')->getContainer($auth['container']);
    }

    /**
     * Get the rackspace adapter.
     *
     * @param \OpenCloud\ObjectStore\Resource\Container $client
     *
     * @return \League\Flysystem\Adapter\Rackspace
     */
    protected function getAdapter(Container $client)
    {
        return new Rackspace($client);
    }
}
