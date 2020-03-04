<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Config;

use Iterator;
use IteratorIterator;
use Traversable;
use Viserio\Contract\Config\Exception\InvalidArgumentException;

class ConfigurationDeprecatedIterator extends IteratorIterator
{
    /**
     * Create a new ConfigurationDeprecatedIterator instance.
     */
    public function __construct(string $class, Traversable $iterator)
    {
        $deprecatedConfig = $class::getDeprecatedConfig();
        $deprecatedConfig = $deprecatedConfig instanceof Iterator ? \iterator_to_array($deprecatedConfig) : (array) $deprecatedConfig;

        if (\count($deprecatedConfig) !== 0) {
            $this->validateDeprecatedKeys($class, $deprecatedConfig, \iterator_to_array($iterator));
        }

        parent::__construct($iterator);
    }

    /**
     * Check if deprecated keys can be found.
     *
     * @param array|Traversable $config
     */
    private function validateDeprecatedKeys(string $class, array $deprecatedConfigs, $config): void
    {
        foreach ($deprecatedConfigs as $key => $deprecationMessage) {
            if (\is_array($deprecationMessage)) {
                $this->validateDeprecatedKeys($class, $deprecationMessage, $config[$key]);

                continue;
            }

            if (\is_int($key)) {
                $key = $deprecationMessage;
                $deprecationMessage = 'The config key [%s] is deprecated.';
            }

            if (! isset($config[$key])) {
                throw new InvalidArgumentException(\sprintf('Config key [%s] cant be deprecated, because it does not exist, in [%s].', $key, $class));
            }

            if (! \is_string($deprecationMessage)) {
                throw new InvalidArgumentException(\sprintf('Invalid deprecation message value provided for [%s]; Expected [string], but got [%s], in [%s].', $key, (\is_object($deprecationMessage) ? \get_class($deprecationMessage) : \gettype($deprecationMessage)), $class));
            }

            if ($deprecationMessage === '' || $deprecationMessage === null) {
                throw new InvalidArgumentException(\sprintf('Deprecation message cant be empty, for config key [%s], in [%s].', $key, $class));
            }

            @\trigger_error(\sprintf($deprecationMessage, $key), \E_USER_DEPRECATED);
        }
    }
}
