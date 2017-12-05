<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Rackspace\RackspaceAdapter;
use OpenCloud\Rackspace;
use stdClass;
use Viserio\Component\Contract\Filesystem\Exception\RuntimeException;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;

class RackspaceConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config): array
    {
        if (! \array_key_exists('username', $config) || ! \array_key_exists('apiKey', $config)) {
            throw new InvalidArgumentException('The rackspace connector requires authentication.');
        }

        if (! \array_key_exists('endpoint', $config)) {
            throw new InvalidArgumentException('The rackspace connector requires endpoint configuration.');
        }

        if (! \array_key_exists('region', $config)) {
            throw new InvalidArgumentException('The rackspace connector requires region configuration.');
        }

        if (! \array_key_exists('container', $config)) {
            throw new InvalidArgumentException('The rackspace connector requires container configuration.');
        }

        return self::getSelectedConfig($config, ['username', 'apiKey', 'endpoint', 'region', 'container', 'internal']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config): array
    {
        if (! \array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\RuntimeException
     *
     * @return \OpenCloud\ObjectStore\Resource\Container
     */
    protected function getClient(array $authConfig): object
    {
        $client = new Rackspace($authConfig['endpoint'], [
            'username' => $authConfig['username'],
            'apiKey'   => $authConfig['apiKey'],
        ]);

        $urlType = ($authConfig['internal'] ?? false) ? 'internalURL' : 'publicURL';

        if ($authConfig['container'] instanceof stdClass || $authConfig['container'] === null) {
            return $client->objectStoreService('cloudFiles', $authConfig['region'], $urlType)
                ->getContainer($authConfig['container']);
        }

        throw new RuntimeException('[OpenCloud\ObjectStore\Service::getContainer] expects only \stdClass or null.');
    }

    /**
     * {@inheritdoc}
     *
     * @param \OpenCloud\ObjectStore\Resource\Container $client
     *
     * @return \League\Flysystem\Rackspace\RackspaceAdapter
     */
    protected function getAdapter(object $client, array $config): AdapterInterface
    {
        return new RackspaceAdapter($client, $config['prefix']);
    }
}
