<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver;

interface Resolver
{
    /**
     * Tell the resolver from wich class he should take the configuration.
     *
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $configClass
     * @param \Interop\Container\ContainerInterface|iterable              $data
     *
     * @return \Viserio\Component\Contracts\OptionsResolver\Resolver
     */
    public function configure(RequiresConfig $configClass, $data): Resolver;

    /**
     * Returns options based on getDimensions() like [vendor][package] if class implements RequiresComponentConfig
     * and can perform mandatory option checks if class implements RequiresMandatoryOptions. If the
     * ProvidesDefaultOptions interface is implemented, these options must be overridden by the provided config.
     * If you want to allow configurations for more then one instance use RequiresConfigId interface.
     *
     * The RequiresConfigId interface is supported.
     *
     * @param null|string $configId Config name, must be provided if factory uses RequiresConfigId interface
     *
     * @throws \InvalidArgumentException                                                                If the $configId parameter is provided but factory does not support it
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException         If the $config parameter has the wrong type
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException          If no options are available
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException If a mandatory option is missing
     *
     * @return array
     */
    public function resolve(string $configId = null): array;
}
