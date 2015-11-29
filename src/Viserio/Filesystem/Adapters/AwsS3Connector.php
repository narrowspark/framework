<?php
namespace Viserio\Filesystem\Adapters;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0
 */

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter as AwsS3v3;
use Viserio\Contracts\Filesystem\Connector as ConnectorContract;
use Viserio\Support\Arr;

/**
 * AwsS3Connector.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.3
 */
class AwsS3Connector implements ConnectorContract
{
    /**
     * Establish an adapter connection.
     *
     * @param array $config
     *
     * @return object
     */
    public function connect(array $config)
    {
        $auth = $this->getAuth($config);
        $client = $this->getClient($auth);
        $config = $this->getConfig($config);

        return $this->getAdapter($client, $config);
    }

    /**
     * Get the authentication data.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getAuth(array $config)
    {
        if (!array_key_exists('key', $config) || !array_key_exists('secret', $config)) {
            throw new \InvalidArgumentException('The awss3 connector requires authentication.');
        }

        if (array_key_exists('region', $config) && array_key_exists('base_url', $config) && array_key_exists('version', $config)) {
            return Arr::only($config, ['key', 'secret', 'region', 'base_url', 'version']);
        }

        if (array_key_exists('region', $config) && array_key_exists('base_url', $config)) {
            return Arr::only($config, ['key', 'secret', 'region', 'base_url']);
        }

        if (array_key_exists('region', $config)) {
            return Arr::only($config, ['key', 'secret', 'region']);
        }

        if (array_key_exists('base_url', $config)) {
            return Arr::only($config, ['key', 'secret', 'base_url']);
        }

        return Arr::only($config, ['key', 'secret']);
    }

    /**
     * Get the awss3 client.
     *
     * @param string[] $auth
     *
     * @return \Aws\S3\S3Client
     */
    protected function getClient(array $auth)
    {
        return new S3Client($auth);
    }

    /**
     * Get the configuration.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return string[]
     */
    protected function getConfig(array $config)
    {
        if (!array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        if (!array_key_exists('bucket', $config)) {
            throw new \InvalidArgumentException('The awss3 connector requires a bucket.');
        }

        if (!array_key_exists('options', $config)) {
            $config['options'] = [];
        }

        return Arr::only($config, ['bucket', 'prefix', 'options']);
    }

    /**
     * Get the awss3 adapter.
     *
     * @param \Aws\S3\S3Client $client
     * @param string[]         $config
     *
     * @return \League\Flysystem\AwsS3v3\AwsS3Adapter
     */
    protected function getAdapter(S3Client $client, array $config)
    {
        return new AwsS3v3($client, $config['bucket'], $config['options']);
    }
}
