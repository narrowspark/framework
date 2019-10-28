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

use League\Flysystem\Adapter\Ftp;
use League\Flysystem\AdapterInterface;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

final class FtpConnector implements ConnectorContract,
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
            'host',
            'port',
            'username',
            'password',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'host' => ['string'],
            'port' => ['string', 'int'],
            'username' => ['string'],
            'password' => ['string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function connect(): AdapterInterface
    {
        return new Ftp($this->resolvedOptions);
    }
}
