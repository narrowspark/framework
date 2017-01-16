<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapters;

use Dropbox\Client;
use InvalidArgumentException;
use League\Flysystem\Dropbox\DropboxAdapter;
use Narrowspark\Arr\Arr;

class DropboxConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config): array
    {
        if (! array_key_exists('token', $config) || ! array_key_exists('app', $config)) {
            throw new InvalidArgumentException('The dropbox connector requires authentication.');
        }

        if (! array_key_exists('locale', $config)) {
            $config['locale'] = null;
        }

        return Arr::only($config, ['token', 'app', 'locale']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $auth)
    {
        return new Client($auth['token'], $auth['app'], $auth['locale']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config): array
    {
        if (! array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        return Arr::only($config, ['prefix']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter($client, array $config): DropboxAdapter
    {
        return new DropboxAdapter($client, $config['prefix']);
    }
}
