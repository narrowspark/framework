<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;

trait StaticOptionsResolverTrait
{
    use AbstractOptionsResolverTrait;

    /**
     * Returns options based on getDimensions() like [vendor][package] if class implements RequiresComponentConfig
     * and can perform mandatory option checks if class implements RequiresMandatoryOptions. If the
     * ProvidesDefaultOptions interface is implemented, these options must be overridden by the provided config.
     * If you want to allow configurations for more then one instance use RequiresConfigId interface.
     *
     * The RequiresConfigId interface is supported.
     *
     * @param mixed       $config
     * @param null|string $configId Config name, must be provided if factory uses RequiresConfigId interface
     *
     * @throws \InvalidArgumentException                                                                If the $configId parameter is provided but factory does not support it
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException         If the $config parameter has the wrong type
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException          If no options are available
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException If a mandatory option is missing
     *
     * @return array
     */
    protected static function resolve($config, string $configId = null): array
    {
        return self::resolveOptions(
            self::resolveConfiguration($config),
            self::getConfigClass(),
            $configId
        );
    }

    /**
     * Resolve the configuration from given data.
     *
     * @param \Psr\Container\ContainerInterface|\ArrayAccess|array $data
     *
     * @throws \RuntimeException Is thrown if config cant be resolved
     *
     * @return array|\ArrayAccess
     */
    abstract protected static function resolveConfiguration($data);

    /**
     * The configurable class.
     *
     * @return \Viserio\Component\Contracts\OptionsResolver\RequiresConfig
     */
    abstract protected static function getConfigClass(): RequiresConfigContract;
}