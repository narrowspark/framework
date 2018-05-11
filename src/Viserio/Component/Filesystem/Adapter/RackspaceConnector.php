<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Rackspace\RackspaceAdapter;
use OpenCloud\Rackspace;
use stdClass;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Component\Contract\Filesystem\Exception\RuntimeException;
use Viserio\Component\Filesystem\Adapter\Traits\GetSelectedConfigTrait;

final class RackspaceConnector implements ConnectorContract
{
    use GetSelectedConfigTrait;

    /**
     * {@inheritdoc}
     */
    public function connect(array $config): AdapterInterface
    {
        $authConfig = $this->getAuth($config);
        $client     = $this->getClient($authConfig);
        $config     = $this->getConfig($config);

        return new RackspaceAdapter($client, $config['prefix']);
    }

    /**
     * Get the authentication data.
     *
     * @param array $config
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return string[]
     */
    private function getAuth(array $config): array
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
     * Get the configuration.
     *
     * @param array $config
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return string[]
     */
    private function getConfig(array $config): array
    {
        if (! \array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        return $config;
    }

    /**
     * Get the client.
     *
     * @param string[] $authConfig
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\RuntimeException
     *
     * @return \OpenCloud\ObjectStore\Resource\Container
     */
    private function getClient(array $authConfig): object
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
}
