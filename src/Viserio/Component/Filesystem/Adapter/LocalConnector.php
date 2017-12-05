<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\Local;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;

class LocalConnector extends AbstractConnector
{
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
            $config['path'],
            $config['write_flags'],
            $config['link_handling'],
            $config['permissions']
        );
    }

    /**
     * {@inheritdoc}
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
    protected function getAdapter(object $client, array $config): AdapterInterface
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
    protected function getClient(array $authConfig): object
    {
    }
}
