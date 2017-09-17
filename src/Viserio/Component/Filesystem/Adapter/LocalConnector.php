<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\Adapter\Local;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;

class LocalConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config): object
    {
        $config = $this->getConfig($config);

        return new Local(
            $config['path'],
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
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getConfig(array $config): array
    {
        if (! \array_key_exists('path', $config)) {
            throw new InvalidArgumentException('The local connector requires path configuration.');
        }

        if (! \array_key_exists('write_flags', $config)) {
            $config['write_flags'] = LOCK_EX;
        }

        if (! \array_key_exists('link_handling', $config)) {
            $config['link_handling'] = Local::DISALLOW_LINKS;
        }

        if (! \array_key_exists('permissions', $config)) {
            $config['permissions'] = [];
        }

        return self::getSelectedConfig($config, ['path', 'write_flags', 'link_handling', 'permissions']);
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
