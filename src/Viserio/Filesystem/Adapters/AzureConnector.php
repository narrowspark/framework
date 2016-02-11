<?php
namespace Viserio\Filesystem\Adapters;

use InvalidArgumentException;
use League\Flysystem\Azure\AzureAdapter;
use Narrowspark\Arr\StaticArr as Arr;
use WindowsAzure\Blob\Internal\IBlob;
use WindowsAzure\Common\ServicesBuilder;

class AzureConnector extends AbstractConnector
{
    /**
     * Get the authentication data.
     *
     * @param string[] $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getAuth(array $config)
    {
        if (!array_key_exists('account-name', $config) || !array_key_exists('api-key', $config)) {
            throw new InvalidArgumentException('The azure connector requires authentication.');
        }

        return Arr::only($config, ['account-name', 'api-key']);
    }

    /**
     * Get the azure client.
     *
     * @param string[] $auth
     *
     * @return \WindowsAzure\Blob\Internal\IBlob
     */
    protected function getClient(array $auth)
    {
        $endpoint = sprintf(
            'DefaultEndpointsProtocol=https;AccountName=%s;AccountKey=%s',
            $auth['account-name'],
            $auth['api-key']
        );

        return ServicesBuilder::getInstance()->createBlobService($endpoint);
    }

    /**
     * Get the configuration.
     *
     * @param string[] $config
     *
     * @return string[]
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('container', $config)) {
            throw new InvalidArgumentException('The azure connector requires container configuration.');
        }

        if (!array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        return Arr::only($config, ['container']);
    }

    /**
     * Get the container adapter.
     *
     * @param \WindowsAzure\Blob\Internal\IBlob $client
     * @param string[]                          $config
     *
     * @return \League\Flysystem\Azure\AzureAdapter
     */
    protected function getAdapter(IBlob $client, array $config)
    {
        return new AzureAdapter($client, $config['container'], $config['prefix']);
    }
}
