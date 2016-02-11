<?php
namespace Viserio\Filesystem\Adapters;

use Aws\S3\S3Client;
use InvalidArgumentException;
use League\Flysystem\AwsS3v3\AwsS3Adapter as AwsS3v3;
use Narrowspark\Arr\StaticArr as Arr;

class AwsS3Connector extends AbstractConnector
{
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
        $this->checkForKeyinConfigArray($config);

        $auth = [
            'region'      => $config['region'],
            'version'     => $config['version'],
            'credentials' => Arr::only($config, ['key', 'secret']),
        ];

        if (array_key_exists('bucket_endpoint', $config)) {
            $auth['bucket_endpoint'] = $config['bucket_endpoint'];
        }

        if (array_key_exists('calculate_md5', $config)) {
            $auth['calculate_md5'] = $config['calculate_md5'];
        }

        if (array_key_exists('scheme', $config)) {
            $auth['scheme'] = $config['scheme'];
        }

        if (array_key_exists('endpoint', $config)) {
            $auth['endpoint'] = $config['endpoint'];
        }

        return $auth;
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
            throw new InvalidArgumentException('The awss3 connector requires a bucket.');
        }

        if (!array_key_exists('options', $config)) {
            $config['options'] = [];
        }

        return Arr::only($config, ['bucket', 'prefix', 'options']);
    }

    /**
     * Get the awss3 adapter.
     *
     * @param object   $client
     * @param string[] $config
     *
     * @return \League\Flysystem\AwsS3v3\AwsS3Adapter
     */
    protected function getAdapter($client, array $config)
    {
        return new AwsS3v3($client, $config['bucket'], $config['prefix'], $config['options']);
    }

    /**
     * Checks for some needed keys in config array.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     */
    private function checkForKeyinConfigArray(array $config)
    {
        if (!array_key_exists('key', $config) || !array_key_exists('secret', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires authentication.');
        }

        if (!array_key_exists('version', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires version configuration.');
        }

        if (!array_key_exists('region', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires region configuration.');
        }
    }
}
