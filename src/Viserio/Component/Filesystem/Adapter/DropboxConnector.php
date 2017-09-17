<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;

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

        return self::getSelectedConfig($config, ['token']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $auth): object
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

        return self::getSelectedConfig($config, ['prefix']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter(object $client, array $config): object
    {
        return new DropboxAdapter($client, $config['prefix']);
    }
}
