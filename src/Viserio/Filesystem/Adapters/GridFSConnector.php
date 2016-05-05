<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\GridFS\GridFSAdapter;
use Narrowspark\Arr\StaticArr as Arr;
use MongoClient;
use Mongo;

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
        $mongo = $this->getMongoClass();

        return new $mongo($auth['server']);
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

    /**
     * Returns the valid Mongo class client for the current php driver.
     *
     * @return string
     */
    protected function getMongoClass()
    {
        if (class_exists(MongoClient::class)) {
            return MongoClient::class;
        }

        return Mongo::class;
    }
}
