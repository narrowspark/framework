<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use InvalidArgumentException;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Narrowspark\Arr\Arr;
use Sabre\DAV\Client;
use Viserio\Component\Contracts\Filesystem\Connector as ConnectorContract;

class WebDavConnector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param string[] $config
     *
     * @return \League\Flysystem\WebDAV\WebDAVAdapter
     */
    public function connect(array $config)
    {
        $client = $this->getClient($config);

        return $this->getAdapter($client);
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $config)
    {
        if (! array_key_exists('baseUri', $config)) {
            throw new InvalidArgumentException('The WebDav connector requires baseUri configuration.');
        }

        if (! array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        return new Client(Arr::only($config, ['prefix', 'baseUri']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter($client): WebDAVAdapter
    {
        return new WebDAVAdapter($client);
    }
}
