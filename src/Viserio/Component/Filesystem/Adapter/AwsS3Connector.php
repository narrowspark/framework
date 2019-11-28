<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem\Adapter;

use Aws\S3\S3Client;
use League\Flysystem\AdapterInterface;
use League\Flysystem\AwsS3v3\AwsS3Adapter as AwsS3v3;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

final class AwsS3Connector implements ConnectorContract,
    ProvidesDefaultOptionContract,
    RequiresConfigContract,
    RequiresMandatoryOptionContract,
    RequiresValidatedOptionContract
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
            'prefix' => null,
            'options' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'prefix' => ['string', 'null'],
            'options' => ['array'],
            'bucket' => ['string'],
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
