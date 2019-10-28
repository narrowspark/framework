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

use League\Flysystem\AdapterInterface;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

final class DropboxConnector implements ConnectorContract,
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
            'token',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'prefix' => '',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'token' => ['string'],
            'prefix' => ['string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function connect(): AdapterInterface
    {
        return new DropboxAdapter(
            new Client($this->resolvedOptions['token']),
            $this->resolvedOptions['prefix']
        );
    }
}
