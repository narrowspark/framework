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

namespace Viserio\Component\Config;

use Iterator;
use IteratorIterator;
use Traversable;
use Viserio\Contract\Config\Exception\InvalidArgumentException;
use Viserio\Contract\Config\Exception\InvalidValidatorException;

class ConfigurationValidatorIterator extends IteratorIterator
{
    /**
     * Map of types to a corresponding function.
     *
     * @var array<string, callable|string>
     */
    private static array $defaultValidatorTypeMap = [
        'resource' => '\is_resource',
        'callable' => '\is_callable',
        'int' => '\is_int',
        'integer' => '\is_int',
        'bool' => '\is_bool',
        'boolean' => '\is_bool',
        'float' => '\is_float',
        'string' => '\is_string',
        'object' => '\is_object',
        'array' => '\is_array',
        'null' => '\is_null',
    ];

    /**
     * Create a new ConfigurationMandatoryIterator instance.
     *
     * @param string      $class
     * @param Traversable $iterator
     */
    public function __construct(string $class, Traversable $iterator)
    {
        $validators = $class::getConfigValidators();
        $validators = $validators instanceof Iterator ? \iterator_to_array($validators) : (array) $validators;

        $this->validateConfig($class, $validators, \iterator_to_array($iterator));

        parent::__construct($iterator);
    }

    /**
     * Run a validator against given config.
     *
     * @param string                                            $class
     * @param array<string, array<int, string>|callback|string> $validators
     * @param iterable|Traversable                              $config
     *
     * @throws \Viserio\Contract\Config\Exception\InvalidValidatorException
     *
     * @return void
     */
    private function validateConfig(string $class, array $validators, $config): void
    {
        foreach ($validators as $key => $values) {
            if (! \is_array($config) || ! \array_key_exists($key, $config)) {
                continue;
            }

            if (! \is_array($values) && ! \is_callable($values)) {
                throw new InvalidValidatorException(\sprintf('The validator must be of type callable or array<string|object, string>; [%s] given, in [%s].', \is_object($values) ? \get_class($values) : \gettype($values), $class));
            }

            if (\is_array($values) && isset($values[0]) && \is_string($values[0])) {
                $hasError = false;

                foreach ($values as $check) {
                    if ($hasError === false && \array_key_exists($check, self::$defaultValidatorTypeMap)) {
                        $hasError = (self::$defaultValidatorTypeMap[$check])($config[$key]);
                    }
                }

                if (! $hasError) {
                    throw InvalidArgumentException::invalidType($key, $config[$key], $values, $class);
                }

                continue;
            }

            if (\is_callable($values)) {
                $values($config[$key], $key);

                continue;
            }

            $this->validateConfig($class, (array) $values, $config[$key]);
        }
    }
}
