<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter as AwsS3v3;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Component\Filesystem\Adapter\Traits\GetSelectedConfigTrait;

final class AwsS3Connector implements ConnectorContract
{
    use GetSelectedConfigTrait;

    /**
     * {@inheritdoc}
     */
    public function connect(array $config): AdapterInterface
    {
        $client = new S3Client($this->getAuth($config));
        $config = $this->getConfig($config);

        return new AwsS3v3(
            $client,
            $config['bucket'],
            $config['prefix'],
            (array) $config['options']
        );
    }

    /**
     * Get the authentication data.
     *
     * @param array $config
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException
     *
     * @return string[]
     */
    private function getAuth(array $config): array
    {
        if (! \array_key_exists('version', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires version configuration.');
        }

        if (! \array_key_exists('region', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires region configuration.');
        }

        $auth = [
            'region'  => $config['region'],
            'version' => $config['version'],
        ];

        if (isset($config['key'])) {
            if (! \array_key_exists('secret', $config)) {
                throw new InvalidArgumentException('The awss3 connector requires authentication.');
            }

            $auth['credentials'] = self::getSelectedConfig($config, ['key', 'secret']);
        }

        if (\array_key_exists('bucket_endpoint', $config)) {
            $auth['bucket_endpoint'] = $config['bucket_endpoint'];
        }

        if (\array_key_exists('calculate_md5', $config)) {
            $auth['calculate_md5'] = $config['calculate_md5'];
        }

        if (\array_key_exists('scheme', $config)) {
            $auth['scheme'] = $config['scheme'];
        }

        if (\array_key_exists('endpoint', $config)) {
            $auth['endpoint'] = $config['endpoint'];
        }

        return $auth;
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

        if (! \array_key_exists('bucket', $config)) {
            throw new InvalidArgumentException('The awss3 connector requires a bucket configuration.');
        }

        if (! \array_key_exists('options', $config)) {
            $config['options'] = [];
        }

        return self::getSelectedConfig($config, ['bucket', 'prefix', 'options']);
    }
}
