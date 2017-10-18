<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use ArrayAccess;
use Iterator;
use Psr\Container\ContainerInterface;
use RuntimeException;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidValidatorException;
use Viserio\Component\Contract\OptionsResolver\Exception\MandatoryOptionNotFoundException;
use Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException;
use Viserio\Component\Contract\OptionsResolver\Exception\UnexpectedValueException;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

/**
 * Some code in this trait is taken from interop-config.
 *
 * @author Sandro Keil https://sandro-keil.de/blog/
 * @copyright Copyright (c) 2015-2017 Sandro Keil
 */
trait OptionsResolverTrait
{
    /**
     * Returns options based on getDimensions() like [vendor][package] if class implements RequiresComponentConfig
     * and can perform mandatory option checks if class implements RequiresMandatoryOptions. If the
     * ProvidesDefaultOptions interface is implemented, these options must be overridden by the provided config.
     * If you want to allow configurations for more then one instance use RequiresConfigId interface.
     *
     * The \Viserio\Component\Contract\OptionsResolver\RequiresConfigId interface is supported.
     *
     * @param mixed       $config
     * @param null|string $configId Config name, must be provided if factory uses RequiresConfigId interface
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException         If the $configId parameter is provided but factory does not support it
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\UnexpectedValueException         If the $config parameter has the wrong type
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException          If no options are available
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\MandatoryOptionNotFoundException If a mandatory option is missing
     *
     * @return array
     */
    public static function resolveOptions($config, string $configId = null): array
    {
        $configClass = self::getConfigClass();
        $interfaces  = \class_implements($configClass);
        $config      = self::resolveConfiguration($config);
        $dimensions  = [];

        if (isset($interfaces[RequiresComponentConfigContract::class])) {
            $dimensions  = $configClass::getDimensions();
            $dimensions  = $dimensions instanceof Iterator ? \iterator_to_array($dimensions) : $dimensions;
        }

        if (isset($interfaces[RequiresConfigIdContract::class]) || isset($interfaces[RequiresComponentConfigIdContract::class])) {
            $dimensions[] = $configId;
        } elseif ($configId !== null) {
            throw new InvalidArgumentException(
                \sprintf('The factory [%s] does not support multiple instances.', $configClass)
            );
        }

        // get configuration for provided dimensions
        $config = self::getConfigurationDimensions($dimensions, $config, $configClass, $interfaces, $configId);

        if ((array) $config !== $config &&
            ! $config instanceof ArrayAccess &&
            isset($interfaces[RequiresComponentConfigIdContract::class])
        ) {
            throw new UnexpectedValueException($configClass::getDimensions());
        }

        if (isset($interfaces[RequiresMandatoryOptionsContract::class])) {
            self::checkMandatoryOptions($configClass, $configClass::getMandatoryOptions(), $config, $interfaces);
        }

        if (isset($interfaces[RequiresValidatedConfigContract::class])) {
            self::validateOptions($configClass::getOptionValidators(), $config, $configClass);
        }

        if (isset($interfaces[ProvidesDefaultOptionsContract::class])) {
            $options = $configClass::getDefaultOptions();
            $config  = \array_replace_recursive(
                $options instanceof Iterator ? \iterator_to_array($options) : (array) $options,
                (array) $config
            );
        }

        return (array) $config;
    }

    /**
     * Resolve the configuration from given data.
     *
     * @param array|\ArrayAccess|\Psr\Container\ContainerInterface $data
     *
     * @throws \RuntimeException is thrown if config cant be resolved
     *
     * @return array|\ArrayAccess
     */
    protected static function resolveConfiguration($data)
    {
        if (\is_iterable($data)) {
            return $data;
        }

        if ($data instanceof ContainerInterface) {
            if ($data->has(RepositoryContract::class)) {
                return $data->get(RepositoryContract::class);
            }

            if ($data->has('config')) {
                return $data->get('config');
            }

            if ($data->has('options')) {
                return $data->get('options');
            }
        }

        throw new RuntimeException('No configuration found.');
    }

    /**
     * The configurable class.
     *
     * @return string
     */
    protected static function getConfigClass(): string
    {
        return static::class;
    }

    /**
     * Checks if a mandatory param is missing, supports recursion.
     *
     * @param string   $configClass
     * @param iterable $mandatoryOptions
     * @param iterable $config
     * @param array    $interfaces
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\MandatoryOptionNotFoundException
     *
     * @return void
     */
    private static function checkMandatoryOptions(
        string $configClass,
        iterable $mandatoryOptions,
        iterable $config,
        array $interfaces
    ): void {
        foreach ($mandatoryOptions as $key => $mandatoryOption) {
            $useRecursion = ! \is_scalar($mandatoryOption);

            if (! $useRecursion && isset($config[$mandatoryOption])) {
                continue;
            }

            if ($useRecursion && isset($config[$key])) {
                self::checkMandatoryOptions($configClass, $mandatoryOption, $config[$key], $interfaces);

                return;
            }

            throw new MandatoryOptionNotFoundException(
                isset($interfaces[RequiresComponentConfigContract::class]) ? $configClass::getDimensions() : [],
                $useRecursion ? $key : $mandatoryOption
            );
        }
    }

    /**
     * Get configuration for provided dimensions.
     *
     * @param iterable    $dimensions
     * @param iterable    $config
     * @param string      $configClass
     * @param null|string $configId
     * @param array       $interfaces
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\UnexpectedValueException
     *
     * @return iterable|object
     */
    private static function getConfigurationDimensions(
        iterable $dimensions,
        $config,
        string $configClass,
        array $interfaces,
        ?string $configId
    ) {
        foreach ($dimensions as $dimension) {
            if ((array) $config !== $config && ! $config instanceof ArrayAccess) {
                throw new UnexpectedValueException($dimensions, $dimension);
            }

            if (! isset($config[$dimension])) {
                if (! isset($interfaces[RequiresMandatoryOptionsContract::class]) &&
                    isset($interfaces[ProvidesDefaultOptionsContract::class])
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
     * Run a validator against given config.
     *
     * @param array    $validators
     * @param iterable $config
     * @param string   $configClass
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidValidatorException
     *
     * @return void
     */
    private static function validateOptions(array $validators, iterable $config, string $configClass): void
    {
        foreach ($validators as $key => $value) {
            $useRecursion = ! \is_scalar($value);

            if (! $useRecursion && isset($config[$value])) {
                continue;
            }

            if ($useRecursion && isset($config[$key])) {
                if (\is_callable($value)) {
                    $value($config[$key]);

                    return;
                }

                self::validateOptions($value, $config[$key], $configClass);

                return;
            }

            if (! \is_callable($value)) {
                throw new InvalidValidatorException(\sprintf(
                    'The validator must be of type callable, [%s] given, in %s.',
                    \is_object($value) ? \get_class($value) : \gettype($value),
                    $configClass
                ));
            }
        }
    }
}
