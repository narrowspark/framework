<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\Rackspace\RackspaceAdapter;
use OpenCloud\Rackspace;
use RuntimeException;
use stdClass;
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
        return $config;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \RuntimeException
     */
    protected function getClient(array $auth): object
    {
        $client = new Rackspace($auth['endpoint'], [
            'username' => $auth['username'],
            'apiKey'   => $auth['apiKey'],
        ]);

        $urlType = ($auth['internal'] ?? false) ? 'internalURL' : 'publicURL';

        if ($auth['container'] instanceof stdClass || $auth['container'] === null) {
            return $client->objectStoreService('cloudFiles', $auth['region'], $urlType)
                ->getContainer($auth['container']);
        }

        throw new RuntimeException('[OpenCloud\ObjectStore\Service::getContainer] expects only stdClass or null.');
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter(object $client, array $config): object
    {
        return new RackspaceAdapter($client);
    }
}
