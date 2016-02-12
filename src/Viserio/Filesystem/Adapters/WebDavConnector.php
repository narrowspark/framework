<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Narrowspark\Arr\StaticArr as Arr;
use Sabre\DAV\Client;

class WebDavConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getClient(array $config)
    {
        return new Client($config);
    }

    protected function getAuth(array $config)
    {
        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        if (!array_key_exists('baseUri', $config)) {
            throw new InvalidArgumentException('The sftp connector requires baseUri configuration.');
        }

        return Arr::only($config, ['prefix', 'baseUri']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter($client, array $config)
    {
        return new WebDAVAdapter($client);
    }
}
