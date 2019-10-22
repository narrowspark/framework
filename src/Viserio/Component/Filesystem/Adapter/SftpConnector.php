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
use League\Flysystem\Sftp\SftpAdapter;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;
use Viserio\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Contract\OptionsResolver\Exception\InvalidArgumentException;
use Viserio\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

final class SftpConnector implements ConnectorContract,
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
            'host',
            'port',
            'username',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'host' => ['string'],
            'port' => ['string', 'integer'],
            'username' => ['string'],
            'password' => ['string'],
            'privateKey' => ['string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function connect(): AdapterInterface
    {
        if (! isset($this->resolvedOptions['password']) && ! isset($this->resolvedOptions['privateKey'])) {
            throw new InvalidArgumentException('The sftp connector requires [password] or [privateKey] configuration.');
        }

        return new SftpAdapter($this->resolvedOptions);
    }
}
