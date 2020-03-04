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
use Iterator;
use IteratorIterator;
use Traversable;

class ConfigurationDefaultIterator extends IteratorIterator
{
    /**
     * Create a new ConfigurationDefaultIterator instance.
     */
    public function __construct(string $class, Traversable $iterator)
    {
        $default = $class::getDefaultConfig();
        $default = $default instanceof Iterator ? \iterator_to_array($default) : (array) $default;

        parent::__construct(
            new ArrayIterator(\array_replace_recursive($default, \iterator_to_array($iterator)))
        );
    }
}
