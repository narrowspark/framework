<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter as AwsS3v3;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\Filesystem\Exception\InvalidArgumentException;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

final class AwsS3Connector implements
    ConnectorContract,
    RequiresConfigContract,
    ProvidesDefaultOptionsContract,
    RequiresMandatoryOptionsContract,
    RequiresValidatedConfigContract
{
    use OptionsResolverTrait;

    /**
     * Resolved options.
     *
     * @var array
     */
    private $resolvedOptions;

    /**
     * Create a new AwsS3Connector instance.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->resolvedOptions = self::resolveOptions($config);
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return [
            'auth' => [
                'version',
                'region',
            ],
            'bucket',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'prefix'  => null,
            'options' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'prefix'  => ['string', 'null'],
            'options' => ['array'],
            'bucket'  => ['string'],
            'auth'    => [
                'credentials' => function ($value) {
                    if (! \is_array($value)) {
                        throw new InvalidArgumentException('.');
                    }

                    if (! isset($value['key']) && ! isset($value['secret'])) {
                        throw new InvalidArgumentException('.');
                    }
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function connect(): AdapterInterface
    {
        $client = new S3Client($this->resolvedOptions['auth']);

        return new AwsS3v3($client, $this->resolvedOptions['bucket'], $this->resolvedOptions['prefix'], $this->resolvedOptions['options']);
    }
}
