<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\Adapter\Ftp;
use Narrowspark\Arr\StaticArr as Arr;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;

class FtpConnector implements ConnectorContract
{
    /**
     * {@inheritdoc}
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
        if (!array_key_exists('host', $config)) {
            throw new InvalidArgumentException('The sftp connector requires host configuration.');
        }

        if (!array_key_exists('port', $config)) {
            throw new InvalidArgumentException('The sftp connector requires port configuration.');
        }

        if (!array_key_exists('username', $config)) {
            throw new InvalidArgumentException('The sftp connector requires username configuration.');
        }

        if (!array_key_exists('password', $config)) {
            throw new InvalidArgumentException('The sftp connector requires password configuration.');
        }

        return Arr::only($config, ['host', 'port', 'username', 'password']);
    }

    /**
     * Get the ftp adapter.
     *
     * @param array $config
     *
     * @return \League\Flysystem\Adapter\Ftp
     */
    protected function getAdapter(array $config)
    {
        return new Ftp($config);
    }
}
