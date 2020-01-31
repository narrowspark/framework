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

use ArrayIterator;
use Iterator;
use IteratorIterator;
use Traversable;

class ConfigurationDefaultIterator extends IteratorIterator
{
    /**
     * Create a new ConfigurationDefaultIterator instance.
     *
     * @param string      $class
     * @param Traversable $iterator
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
