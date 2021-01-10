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

namespace Viserio\Provider\Twig\RuntimeLoader;

use IteratorAggregate;
use Twig\RuntimeLoader\RuntimeLoaderInterface;

class IteratorRuntimeLoader implements RuntimeLoaderInterface
{
    /** @var IteratorAggregate */
    private $iterator;

    /**
     * @param IteratorAggregate $iterator Indexed by command names
     */
    public function __construct(IteratorAggregate $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritdoc}
     */
    public function load($class)
    {
        foreach ($this->iterator as $id => $value) {
            if ($id === $class) {
                return $value;
            }
        }

        return null;
    }
}
