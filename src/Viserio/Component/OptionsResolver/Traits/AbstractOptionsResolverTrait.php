<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use ArrayAccess;
use InvalidArgumentException;
use Iterator;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException;
use Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

/**
 * Some code in this trait is taken from interop-config.
 *
 * @author Sandro Keil https://sandro-keil.de/blog/
 */
trait AbstractOptionsResolverTrait
{
    /**
     * Returns options based on getDimensions() like [vendor][package] if class implements RequiresComponentConfig
     * and can perform mandatory option checks if class implements RequiresMandatoryOptions. If the
     * ProvidesDefaultOptions interface is implemented, these options must be overridden by the provided config.
     * If you want to allow configurations for more then one instance use RequiresConfigId interface.
     *
     * The \Viserio\Component\Contracts\OptionsResolver\RequiresConfigId interface is supported.
     *
     * @param mixed                                                       $config
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $configClass
     * @param null|string                                                 $configId    Config name, must be provided if factory uses RequiresConfigId interface
     *
     * @throws \InvalidArgumentException                                                                If the $configId parameter is provided but factory does not support it
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\UnexpectedValueException         If the $config parameter has the wrong type
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\OptionNotFoundException          If no options are available
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException If a mandatory option is missing
     *
     * @return array
     */
    protected static function resolveOptions($config, RequiresConfigContract $configClass, string $configId = null): array
    {
        $dimensions  = [];

        if ($configClass instanceof RequiresComponentConfigContract) {
            $dimensions  = $configClass->getDimensions();
            $dimensions  = $dimensions instanceof Iterator ? iterator_to_array($dimensions) : $dimensions;
        }

        if ($configClass instanceof RequiresConfigIdContract ||
            $configClass instanceof RequiresComponentConfigIdContract
        ) {
            $dimensions[] = $configId;
        } elseif ($configId !== null) {
            throw new InvalidArgumentException(
                sprintf('The factory [%s] does not support multiple instances.', get_class($configClass))
            );
        }

        // get configuration for provided dimensions
        $config = self::getConfigurationDimensions($dimensions, $config, $configClass, $configId);

        if ((array) $config !== $config &&
            ! $config instanceof ArrayAccess &&
            $configClass instanceof RequiresComponentConfigIdContract
        ) {
            throw new UnexpectedValueException($configClass->getDimensions());
        }

        if ($configClass instanceof RequiresMandatoryOptionsContract) {
            self::checkMandatoryOptions($configClass, $configClass->getMandatoryOptions(), $config);
        }

        if ($configClass instanceof RequiresValidatedConfigContract) {
            self::validateOptions($config);
        }

        if ($configClass instanceof ProvidesDefaultOptionsContract) {
            $options = $configClass->getDefaultOptions();
            $config  = array_replace_recursive(
                $options instanceof Iterator ? iterator_to_array($options) : (array) $options,
                (array) $config
            );
        }

        return (array) $config;
    }

    /**
     * Checks if a mandatory param is missing, supports recursion.
     *
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $configClass
     * @param iterable                                                    $mandatoryOptions
     * @param iterable                                                    $config
     *
     * @throws \Viserio\Component\Contracts\OptionsResolver\Exceptions\MandatoryOptionNotFoundException
     *
     * @return void
     */
    private static function checkMandatoryOptions(RequiresConfigContract $configClass, iterable $mandatoryOptions, iterable $config): void
    {
        foreach ($mandatoryOptions as $key => $mandatoryOption) {
            $useRecursion = ! is_scalar($mandatoryOption);

            if (! $useRecursion && isset($config[$mandatoryOption])) {
                continue;
            }

            if ($useRecursion && isset($config[$key])) {
                self::checkMandatoryOptions($configClass, $mandatoryOption, $config[$key]);

                return;
            }

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
    private static function getConfigurationDimensions(
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
     * Run the validators against given config.
     *
     * @param iterable                                                    $config
     * @param \Viserio\Component\Contracts\OptionsResolver\RequiresConfig $configClass
     *
     * @return void
     */
    private static function validateOptions(iterable $config, RequiresConfigContract $configClass): void
    {
        $valid = true;

        foreach ($configClass->getOptionValidators() as $key => $callable) {
            if (isset($config[$key])) {
                // code...
            }

            $valid = $callable($config[$key]);
        }

        if ($valid === false) {
            // code...
        }
    }
}