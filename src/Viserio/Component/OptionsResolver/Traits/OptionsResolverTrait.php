<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Traits;

use ArrayAccess;
use Viserio\Component\Contract\OptionsResolver\DeprecatedOptions as DeprecatedOptionsContract;
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

trait OptionsResolverTrait
{
    /**
     * Map of types to a corresponding function.
     *
     * @var array
     */
    private static $defaultTypeMap = [
        'resource' => '\is_resource',
        'callable' => '\is_callable',
        'int'      => '\is_int',
        'integer'  => '\is_int',
        'bool'     => '\is_bool',
        'boolean'  => '\is_bool',
        'float'    => '\is_float',
        'string'   => '\is_string',
        'object'   => '\is_object',
        'array'    => '\is_array',
        'null'     => '\is_null',
    ];

    /**
     * Returns options based on getDimensions() like [vendor][package] if class implements RequiresComponentConfig
     * and can perform mandatory option checks if class implements RequiresMandatoryOptions. If the
     * ProvidesDefaultOptions interface is implemented, these options must be overridden by the provided config.
     * If you want to allow configurations for more then one instance use RequiresConfigId interface.
     *
     * The \Viserio\Component\Contract\OptionsResolver\RequiresConfigId interface is supported.
     *
     * @param array|\ArrayAccess $config
     * @param null|string        $configId Config name, must be provided if factory uses RequiresConfigId interface
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
        $dimensions  = [];

        if (isset($interfaces[RequiresComponentConfigContract::class])) {
            $dimensions = $configClass::getDimensions();
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
            $config = \array_replace_recursive($configClass::getDefaultOptions(), (array) $config);
        }

        if (isset($interfaces[DeprecatedOptionsContract::class])) {
            self::checkDeprecatedOptions($configClass, $configClass::getDeprecatedOptions(), $config);
        }

        return (array) $config;
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
     * @param string             $configClass
     * @param array              $mandatoryOptions
     * @param array|\ArrayAccess $config
     * @param array              $interfaces
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\MandatoryOptionNotFoundException
     *
     * @return void
     */
    private static function checkMandatoryOptions(
        string $configClass,
        array $mandatoryOptions,
        $config,
        array $interfaces
    ): void {
        foreach ($mandatoryOptions as $key => $mandatoryOption) {
            $useRecursion = ! \is_scalar($mandatoryOption);

            if (! $useRecursion && \array_key_exists($mandatoryOption, (array) $config)) {
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
     * @param array              $dimensions
     * @param array|\ArrayAccess $config
     * @param string             $configClass
     * @param null|string        $configId
     * @param array              $interfaces
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\UnexpectedValueException
     *
     * @return array|\ArrayAccess
     */
    private static function getConfigurationDimensions(
        array $dimensions,
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
     * @param array<string => array<int => string>, string => callback> $validators
     * @param array|\ArrayAccess                                        $config
     * @param string                                                    $configClass
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidValidatorException
     *
     * @return void
     */
    private static function validateOptions(array $validators, $config, string $configClass): void
    {
        foreach ($validators as $key => $values) {
            if (! \array_key_exists($key, (array) $config)) {
                continue;
            }

            if (\is_array($values) && isset($values[0]) && \is_string($values[0])) {
                $hasError = false;

                foreach ($values as $check) {
                    if ($hasError === false && \array_key_exists($check, self::$defaultTypeMap)) {
                        $fn       = self::$defaultTypeMap[$check];
                        $hasError = $fn($config[$key]);
                    }
                }

                if (! $hasError) {
                    throw InvalidArgumentException::invalidType($key, $config[$key], $values, $configClass);
                }

                continue;
            }

            if (\is_callable($values)) {
                $values($config[$key]);

                continue;
            }

            if (! \is_array($values) && ! \is_callable($values)) {
                throw new InvalidValidatorException(\sprintf(
                    'The validator must be of type callable or string[]; [%s] given, in [%s].',
                    \is_object($values) ? \get_class($values) : \gettype($values),
                    $configClass
                ));
            }

            self::validateOptions((array) $values, $config[$key], $configClass);
        }
    }

    /**
     * Checks if a deprecation exists and triggers a deprecation error.
     *
     * @param string             $configClass
     * @param array              $deprecatedOptions
     * @param array|\ArrayAccess $config
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException If deprecation message cant be found or
     *                                                                                        the key don't exists in the config array
     *
     * @return void
     */
    private static function checkDeprecatedOptions(string $configClass, array $deprecatedOptions, $config): void
    {
        foreach ($deprecatedOptions as $key => $deprecationMessage) {
            if (\is_array($deprecationMessage)) {
                self::checkDeprecatedOptions($configClass, $deprecationMessage, $config[$key]);

                continue;
            }

            if (\is_int($key)) {
                $key                = $deprecationMessage;
                $deprecationMessage = 'The option [%s] is deprecated.';
            }

            if (! \array_key_exists($key, (array) $config)) {
                throw new InvalidArgumentException(\sprintf(
                    'Option [%s] cant be deprecated, because it does not exist, in [%s].',
                    $key,
                    $configClass
                ));
            }

            if (! \is_string($deprecationMessage)) {
                throw new InvalidArgumentException(\sprintf(
                    'Invalid deprecation message value provided for [%s]; Expected [string], but got [%s], in [%s].',
                    $key,
                    (\is_object($deprecationMessage) ? \get_class($deprecationMessage) : \gettype($deprecationMessage)),
                    $configClass
                ));
            }

            if (empty($deprecationMessage)) {
                throw new InvalidArgumentException(\sprintf(
                    'Deprecation message cant be empty, for option [%s], in [%s].',
                    $key,
                    $configClass
                ));
            }

            @\trigger_error(\sprintf($deprecationMessage, $key), \E_USER_DEPRECATED);
        }
    }
}
