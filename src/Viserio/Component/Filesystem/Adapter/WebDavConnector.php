<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use Viserio\Component\Contracts\Filesystem\Exception\InvalidArgumentException;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;

class WebDavConnector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    public function connect(array $config): object
    {
        $client = $this->getClient($config);

        return new WebDAVAdapter($client);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config): array
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $config): object
    {
        if (! \array_key_exists('baseUri', $config)) {
            throw new InvalidArgumentException('The WebDav connector requires baseUri configuration.');
        }

        if (! \array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        return new Client(self::getSelectedConfig($config, ['prefix', 'baseUri']));
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter(object $client, array $config): object
    {
        //
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config): array
    {
        //
    }
}
