<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\Sftp\SftpAdapter;
use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;

class SftpConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param string[] $config
     *
     * @return object
     */
    public function connect(array $config)
    {
        $config = $this->getConfig($config);

        return $this->getAdapter($config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config)
    {
        if (! array_key_exists('host', $config)) {
            throw new InvalidArgumentException('The sftp connector requires host configuration.');
        }

        if (! array_key_exists('port', $config)) {
            throw new InvalidArgumentException('The sftp connector requires port configuration.');
        }

        if (! array_key_exists('username', $config)) {
            throw new InvalidArgumentException('The sftp connector requires username configuration.');
        }

        if ($pw = ! array_key_exists('password', $config)) {
            if (! array_key_exists('privateKey', $config) && $pw) {
                throw new InvalidArgumentException('The sftp connector requires password or privateKey configuration.');
            }
        }

        return Arr::only($config, ['host', 'port', 'username', 'password', 'privateKey']);
    }

    /**
     * Get the sftp adapter.
     *
     * @param string[] $config
     *
     * @return \League\Flysystem\Sftp\SftpAdapter
     */
    protected function getAdapter(array $config): \League\Flysystem\Sftp\SftpAdapter
    {
        return new SftpAdapter($config);
    }
}
