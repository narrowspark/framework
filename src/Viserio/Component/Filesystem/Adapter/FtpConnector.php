<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\Adapter\Ftp;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;

class FtpConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config): object
    {
        $config = $this->getConfig($config);

        return new Ftp($config);
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

        if (! \array_key_exists('password', $config)) {
            throw new InvalidArgumentException('The sftp connector requires password configuration.');
        }

        return self::getSelectedConfig($config, ['host', 'port', 'username', 'password']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter(object $client, array $config): object
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config): array
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $auth): object
    {
    }
}
