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

use ArrayIterator;
use IteratorIterator;
use Traversable;
use Viserio\Contract\Config\DeprecatedConfig as DeprecatedConfigContract;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\Config\RequiresValidatedConfig as RequiresValidatedConfigContract;

class ConfigurationIterator extends IteratorIterator
{
    /**
     * Create a new ConfigurationIterator instance.
     *
     * @param iterable|Traversable $iterator
     */
    public function __construct(string $class, $iterator, ?string $id = null)
    {
        if (\is_array($iterator) || (! \is_iterable($iterator) && ! $iterator instanceof Traversable)) {
            $iterator = new ArrayIterator($iterator);
        }

        $interfaces = \class_implements($class);

        if (\array_key_exists(RequiresComponentConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationDimensionsIterator($class, $iterator, $id);
        }

        if (\array_key_exists(RequiresMandatoryConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationMandatoryIterator($class, $iterator);
        }

        if (\array_key_exists(RequiresValidatedConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationValidatorIterator($class, $iterator);
        }

        if (\array_key_exists(ProvidesDefaultConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationDefaultIterator($class, $iterator);
        }

        if (\array_key_exists(DeprecatedConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationDeprecatedIterator($class, $iterator);
        }

        parent::__construct($iterator);
    }
}
