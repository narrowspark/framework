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

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

final class LocalConnector implements ConnectorContract,
    ProvidesDefaultOptionsContract,
    RequiresConfigContract,
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
            'path',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'write_flags' => \LOCK_EX,
            'link_handling' => Local::DISALLOW_LINKS,
            'permissions' => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'path' => ['string'],
            'write_flags' => ['int'],
            'link_handling' => ['int'],
            'permissions' => ['array'],
        ];
    }

    /**
     * Establish a connection.
     *
     * @throws \Viserio\Contract\OptionsResolver\Exception\InvalidArgumentException On wrong configuration
     *
     * @return \League\Flysystem\Adapter\Local
     */
    public function connect(): AdapterInterface
    {
        return new Local(
            $this->resolvedOptions['path'],
            $this->resolvedOptions['write_flags'],
            $this->resolvedOptions['link_handling'],
            $this->resolvedOptions['permissions']
        );
    }
}
