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
     * {@inheritdoc}
     */
    protected function getAuth(array $config)
    {
        if (!array_key_exists('account-name', $config) || !array_key_exists('api-key', $config)) {
            throw new InvalidArgumentException('The azure connector requires authentication.');
        }

        return Arr::only($config, ['account-name', 'api-key']);
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function getAdapter($client, array $config)
    {
        return new AzureAdapter($client, $config['container'], $config['prefix']);
    }
}
