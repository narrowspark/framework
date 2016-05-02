<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\Rackspace\RackspaceAdapter;
use Narrowspark\Arr\StaticArr as Arr;
use OpenCloud\ObjectStore\Resource\Container;
use OpenCloud\OpenStack;

class RackspaceConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config)
    {
        if (!array_key_exists('username', $config) || !array_key_exists('apiKey', $config)) {
            throw new InvalidArgumentException('The rackspace connector requires authentication.');
        }

        if (!array_key_exists('endpoint', $config)) {
            throw new InvalidArgumentException('The rackspace connector requires endpoint configuration.');
        }

        if (!array_key_exists('region', $config)) {
            throw new InvalidArgumentException('The rackspace connector requires region configuration.');
        }

        if (!array_key_exists('container', $config)) {
            throw new InvalidArgumentException('The rackspace connector requires container configuration.');
        }

        return Arr::only($config, ['username', 'apiKey', 'endpoint', 'region', 'container', 'internal']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config)
    {
        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $auth)
    {
        $client = new OpenStackRackspace($auth['endpoint'], [
            'username' => $auth['username'],
            'apiKey'   => $auth['apiKey'],
        ]);

        $urlType = Arr::get($auth, 'internal', false) ? 'internalURL' : 'publicURL';

        return $client->objectStoreService('cloudFiles', $auth['region'], $urlType)->getContainer($auth['container']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter($client, array $config)
    {
        return new Rackspace($client);
    }
}
