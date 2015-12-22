<?php
namespace Viserio\Connect\Adapters;

use Memcached;
use RuntimeException;
use Viserio\Contracts\Connect\Connector as ConnectorContract;

class MemcachedConnector implements ConnectorContract
{
    /**
     * Establish a connection.
     *
     * @param array $config
     *
     * @throws \RuntimeException
     *
     * @return \Memcached
     */
    public function connect(array $config)
    {
        $id = array_key_exists('persistent_id', $config) ? $config['persistent_id'] : null;

        $memcached = $this->getMemcached($id);

        if (isset($config['options'])) {
            $memcached = $this->addMemcachedOptions($memcached, $config['options']);
        }

        if (isset($config['sasl'])) {
            if (array_key_exists('username', $config['sasl']) && array_key_exists('passowrt', $config['sasl'])) {
                $memcached = $this->addSaslAuth($memcached, $config);
            }
        }

        // Only add servers if we need to. If using a persistent connection
        // the servers must only be added the first time otherwise connections
        // are duplicated.
        if (!$memcached->getServerList() && isset($config['servers'])) {
            $memcached = $this->addMemcachedServers($memcached, $config['servers']);
        }

        // Verify connection
        $memcachedStatus = $memcached->getVersion();

        if (!is_array($memcachedStatus)) {
            throw new RuntimeException('No Memcached servers added.');
        }

        if (in_array('255.255.255', $memcachedStatus, true) && count(array_unique($memcachedStatus)) === 1) {
            throw new RuntimeException('Could not establish Memcached connection.');
        }

        return $memcached;
    }

    /**
     * For each server in the array, we'll just extract the configuration and add
     * the server to the Memcached connection. Once we have added all of these
     * servers we'll verify the connection is successful and return it back.
     *
     * @param object $memcached
     * @param array  $servers
     *
     * @return object
     */
    protected function addMemcachedServers($memcached, array $servers)
    {
        foreach ($servers as $server) {
            $memcached->addServer(
                $server['host'],
                $server['port'],
                $server['weight']
            );
        }

        return $memcached;
    }

    /**
     * Set custom memcached options.
     *
     * @param object $memcached
     * @param array  $config
     *
     * @throws \RuntimeException
     *
     * @return object
     */
    protected function addMemcachedOptions($memcached, array $config)
    {
        $memcachedConstants = array_map(
            function ($option) {
                $constant = "Memcached::{$option}";

                if (!defined($constant)) {
                    throw new RuntimeException("Invalid Memcached option: [{$constant}]");
                }

                return constant($constant);
            },
            array_keys($config)
        );

        $memcached->setOptions(array_combine($memcachedConstants, $config));

        return $memcached;
    }

    /**
     * Set SASL auth data, requires binary protocol.
     *
     * @param object $memcached
     * @param array  $config
     *
     * @return object
     */
    protected function addSaslAuth(object $memcached, array $config)
    {
        $memcached->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
        $memcached->setSaslAuthData($config['sasl']['username'], $config['sasl']['passowrt']);

        return $memcached;
    }

    /**
     * Get a new Memcached instance.
     *
     * @param null|string $persistentConnectionId
     *
     * @return \Memcached
     */
    protected function getMemcached($persistentConnectionId = null)
    {
        if ($persistentConnectionId) {
            return new Memcached($persistentConnectionId);
        }

        return new Memcached();
    }
}
