<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver;

use ArrayAccess;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\Resolver as ResolverContract;

abstract class AbstractOptionsResolver implements ResolverContract
{
    /**
     * Configurable class.
     *
     * @var \Viserio\Component\Contracts\OptionsResolver\RequiresConfig
     */
    protected $configClass;

    /**
     * All of the configuration items.
     *
     * @var iterable
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public function configure(RequiresConfigContract $configClass, $data): ResolverContract
    {
        $this->configClass = $configClass;
        $this->config      = $this->resolveConfiguration($data);

        return $this;
    }

    /**
     * Checks if a mandatory param is missing, supports recursion.
     *
     * @param iterable $mandatoryOptions
     * @param iterable $config
     *
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException
     *
     * @return void
     */
    protected function checkMandatoryOptions(iterable $mandatoryOptions, iterable $config): void
    {
        foreach ($mandatoryOptions as $key => $mandatoryOption) {
            $useRecursion = ! is_scalar($mandatoryOption);

            if (! $useRecursion && isset($config[$mandatoryOption])) {
                continue;
            }

            if ($useRecursion && isset($config[$key])) {
                $this->checkMandatoryOptions($mandatoryOption, $config[$key]);

                return;
            }

            $configClass = $this->configClass;

            throw new MandatoryOptionNotFoundException(
                $configClass instanceof RequiresComponentConfigContract ? $configClass->getDimensions() : [],
                $useRecursion ? $key : $mandatoryOption
            );
        }
    }

    /**
     * Get configuration for provided dimensions.
     *
     * @param iterable                                                    $dimensions
     * @param iterable                                                    $config
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $configClass
     * @param null|string                                                 $configId
     *
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException
     *
     * @return iterable|object
     */
    protected function getConfigurationDimensions(
        iterable $dimensions,
        $config,
        RequiresConfigContract $configClass,
        ?string $configId
    ) {
        foreach ($dimensions as $dimension) {
            if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
                throw new UnexpectedValueException($dimensions, $dimension);
            }

            if (! isset($config[$dimension])) {
                if (! $configClass instanceof RequiresMandatoryOptionsContract &&
                    $configClass instanceof ProvidesDefaultOptionsContract
                ) {
                    break;
                }

                throw new OptionNotFoundException($configClass, $dimension, $configId);
            }

            $config = $config[$dimension];
        }

        return $config;
    }

    /**
     * Get resolve the right configuration data.
     *
     * @param \Psr\Container\ContainerInterface|\ArrayAccess|array $data
     *
     * @throws \RuntimeException
     *
     * @return array|\ArrayAccess
     */
    abstract protected function resolveConfiguration($data);
}
