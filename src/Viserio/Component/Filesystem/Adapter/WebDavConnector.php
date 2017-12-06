<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use InvalidArgumentException as BaseInvalidArgumentException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\WebDAV\WebDAVAdapter;
use Sabre\DAV\Client;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Component\Filesystem\Adapter\Traits\GetSelectedConfigTrait;

final class WebDavConnector implements ConnectorContract
{
    use GetSelectedConfigTrait;

    /**
     * {@inheritdoc}
     */
    public function connect(array $config): AdapterInterface
    {
        $client = $this->getClient($config);
        $config = $this->getConfig($config);

        return $this->getAdapter($client, $config);
    }

    /**
     * Get the configuration.
     *
     * @param array $config
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return string[]
     */
    private function getConfig(array $config): array
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
     * Get the client.
     *
     * @param string[] $authConfig
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return \Sabre\DAV\Client
     */
    private function getClient(array $authConfig): Client
    {
        try {
            return new Client($authConfig);
        } catch (BaseInvalidArgumentException $exception) {
            throw new InvalidArgumentException($exception->getMessage() . '.');
        }
    }

    /**
     * Get the adapter.
     *
     * @param \Sabre\DAV\Client $client
     * @param string[]          $config
     *
     * @return \League\Flysystem\WebDAV\WebDAVAdapter
     */
    private function getAdapter(Client $client, array $config): WebDAVAdapter
    {
        return new WebDAVAdapter($client, $config['prefix'], $config['use_streamed_copy']);
    }
}
