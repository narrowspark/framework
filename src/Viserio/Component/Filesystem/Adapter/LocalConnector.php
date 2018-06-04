<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Component\Filesystem\Adapter\Traits\GetSelectedConfigTrait;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

final class LocalConnector implements ConnectorContract
{
    use GetSelectedConfigTrait;
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     *
     * @return \League\Flysystem\Adapter\Local
     */
    public function connect(array $config): AdapterInterface
    {
        $config = $this->getConfig($config);

        return new Local(
            self::normalizeDirectorySeparator($config['path']),
            $config['write_flags'],
            $config['link_handling'],
            $config['permissions']
        );
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
        if (! \array_key_exists('path', $config)) {
            throw new InvalidArgumentException('The local connector requires path configuration.');
        }

        if (! \array_key_exists('write_flags', $config)) {
            $config['write_flags'] = \LOCK_EX;
        }

        if (! \array_key_exists('link_handling', $config)) {
            $config['link_handling'] = Local::DISALLOW_LINKS;
        }

        if (! \array_key_exists('permissions', $config)) {
            $config['permissions'] = [];
        }

        return self::getSelectedConfig($config, ['path', 'write_flags', 'link_handling', 'permissions']);
    }
}
