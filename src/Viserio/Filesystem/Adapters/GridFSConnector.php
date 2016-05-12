<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\GridFS\GridFSAdapter;
use MongoClient;
use Narrowspark\Arr\StaticArr as Arr;

class GridFSConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config)
    {
        if (!array_key_exists('server', $config)) {
            throw new InvalidArgumentException('The gridfs connector requires server configuration.');
        }

        return Arr::only($config, ['server']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $auth)
    {
        return new MongoClient($auth['server']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('database', $config)) {
            throw new InvalidArgumentException('The gridfs connector requires database configuration.');
        }

        return Arr::only($config, ['database']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter($client, array $config)
    {
        return new GridFSAdapter($client->selectDB($config['database'])->getGridFS());
    }
}
