<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Adapter;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Rackspace\RackspaceAdapter;
use OpenCloud\ObjectStore\Resource\Container;
use OpenCloud\Rackspace;
use stdClass;
use Viserio\Component\Contract\Filesystem\Connector as ConnectorContract;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

final class RackspaceConnector implements
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
            'username',
            'apiKey',
            'endpoint',
            'region',
            'container',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            'urlType' => null,
            'options' => [],
            'prefix'  => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getOptionValidators(): array
    {
        return [
            'username'  => ['string'],
            'apiKey'    => ['string'],
            'endpoint'  => ['string'],
            'region'    => ['string'],
            'container' => function ($value) {
                if (! $value instanceof stdClass && $value !== null) {
                    throw new InvalidArgumentException('[OpenCloud\ObjectStore\Service::getContainer] expects only \stdClass or null.');
                }
            },
            'urlType'  => ['string', 'null'],
            'options'  => ['array'],
            'prefix'   => ['string', 'null'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function connect(): AdapterInterface
    {
        return new RackspaceAdapter($this->getContainer(), $this->resolvedOptions['prefix']);
    }

    /**
     * Get the OpenCloud container.
     *
     * @throws \Viserio\Component\Contract\Filesystem\Exception\RuntimeException
     *
     * @return \OpenCloud\ObjectStore\Resource\Container
     */
    private function getContainer(): Container
    {
        $client = new Rackspace(
            $this->resolvedOptions['endpoint'],
            [
                'username' => $this->resolvedOptions['username'],
                'apiKey'   => $this->resolvedOptions['apiKey'],
            ]
        );

        $service = $client->objectStoreService(
            'cloudFiles',
            $this->resolvedOptions['region'],
            $this->resolvedOptions['urlType']
        );

        return $service->getContainer($this->resolvedOptions['container']);
    }
}
