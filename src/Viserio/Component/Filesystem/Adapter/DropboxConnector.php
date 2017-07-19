<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use InvalidArgumentException;
use Narrowspark\Arr\Arr;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

class DropboxConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config): array
    {
        if (! \array_key_exists('token', $config)) {
            throw new InvalidArgumentException('The dropbox connector requires authentication token.');
        }

        return Arr::only($config, ['token']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $auth)
    {
        return new Client($auth['token']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config): array
    {
        if (! \array_key_exists('prefix', $config)) {
            $config['prefix'] = '';
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
