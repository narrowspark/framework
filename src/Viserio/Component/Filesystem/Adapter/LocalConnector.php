<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

final class LocalConnector implements
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
            'path',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'write_flags'   => \LOCK_EX,
            'link_handling' => Local::DISALLOW_LINKS,
            'permissions'   => [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'host'     => ['string'],
            'port'     => ['string', 'int'],
            'username' => ['string'],
            'password' => ['string'],
        ];
    }

    /**
     * Establish a connection.
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException On wrong configuration
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
