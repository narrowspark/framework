<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use InvalidArgumentException;
use League\Flysystem\Sftp\SftpAdapter;
use Narrowspark\Arr\Arr;
use Viserio\Component\Contracts\Filesystem\Connector as ConnectorContract;

class SftpConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param string[] $config
     *
     * @return SftpAdapter
     */
    public function connect(array $config)
    {
        $config = $this->getConfig($config);

        return $this->getAdapter($config);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config): array
    {
        if (! \array_key_exists('host', $config)) {
            throw new InvalidArgumentException('The sftp connector requires host configuration.');
        }

        if (! \array_key_exists('port', $config)) {
            throw new InvalidArgumentException('The sftp connector requires port configuration.');
        }

        if (! \array_key_exists('username', $config)) {
            throw new InvalidArgumentException('The sftp connector requires username configuration.');
        }

        if ($pw = ! \array_key_exists('password', $config)) {
            if (! \array_key_exists('privateKey', $config) && $pw) {
                throw new InvalidArgumentException('The sftp connector requires password or privateKey configuration.');
            }
        }

        return Arr::only($config, ['host', 'port', 'username', 'password', 'privateKey']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter(array $config): SftpAdapter
    {
        return new SftpAdapter($config);
    }
}
