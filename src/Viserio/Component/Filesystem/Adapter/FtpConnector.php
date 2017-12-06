<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\AdapterInterface;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Component\Filesystem\Adapter\Traits\GetSelectedConfigTrait;

final class FtpConnector implements ConnectorContract
{
    use GetSelectedConfigTrait;

    /**
     * {@inheritdoc}
     */
    public function connect(array $config): AdapterInterface
    {
        $config = $this->getConfig($config);

        return new Ftp($config);
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
}
