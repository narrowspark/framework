<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;

abstract class AbstractConnector implements ConnectorContract
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config): object
    {
        $auth   = $this->getAuth($config);
        $client = $this->getClient($auth);
        $config = $this->getConfig($config);

        return $this->getAdapter($client, $config);
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
    abstract protected function getAuth(array $config): array;

    /**
     * Get the client.
     *
     * @param string[] $auth
     *
     * @return object
     */
    abstract protected function getClient(array $auth): object;

    /**
     * Get the configuration.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    abstract protected function getConfig(array $config): array;

    /**
     * Get the adapter.
     *
     * @param object   $client
     * @param string[] $config
     *
     * @return object
     */
    abstract protected function getAdapter(object $client, array $config): object;

    /**
     * Get a subset of the items from the given array.
     *
     * @param array    $config
     * @param string[] $keys
     *
     * @return string[]
     */
    protected static function getSelectedConfig(array $config, array $keys): array
    {
        return array_intersect_key($config, array_flip($keys));
    }
}
