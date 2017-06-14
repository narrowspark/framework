<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use Aws\S3\S3Client;
use InvalidArgumentException;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter as AwsS3v3;
use Narrowspark\Arr\Arr;

class AwsS3Connector extends AbstractConnector
{
    /**
     * {@inheritdoc}
     */
    protected function getAuth(array $config): array
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
     * {@inheritdoc}
     */
    protected function getClient(array $auth)
    {
        return new S3Client($auth);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfig(array $config): array
    {
        if (! array_key_exists('prefix', $config)) {
            $config['prefix'] = null;
        }

        if (! array_key_exists('bucket', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires a bucket configuration.');
        }

        if (! array_key_exists('options', $config)) {
            $config['options'] = [];
        }

        return Arr::only($config, ['bucket', 'prefix', 'options']);
    }

    /**
     * {@inheritdoc}
     */
    protected function getAdapter($client, array $config): AdapterInterface
    {
        return new AwsS3v3($client, $config['bucket'], $config['prefix'], (array) $config['options']);
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
        if (! array_key_exists('key', $config) || ! array_key_exists('secret', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires authentication.');
        }

        if (! array_key_exists('version', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires version configuration.');
        }

        if (! array_key_exists('region', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires region configuration.');
        }
    }
}
