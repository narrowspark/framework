<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use InvalidArgumentException as BaseInvalidArgumentException;

class WebDavConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config): array
    {
        if (! \array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        if (! \array_key_exists('use_streamed_copy', $config)) {
            $config['use_streamed_copy'] = true;
        }

        return self::getSelectedConfig($config, ['prefix', 'use_streamed_copy']);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \Sabre\DAV\Client
     */
    protected function getClient(array $authConfig): object
    {
        try {
            return new Client($authConfig);
        } catch (BaseInvalidArgumentException $exception) {
            throw new InvalidArgumentException($exception->getMessage() . '.');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @var \Sabre\DAV\Client $client
     */
    protected function getAdapter(object $client, array $config): AdapterInterface
    {
        return new WebDAVAdapter($client, $config['prefix'], $config['use_streamed_copy']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config): array
    {
        return $config;
    }
}
