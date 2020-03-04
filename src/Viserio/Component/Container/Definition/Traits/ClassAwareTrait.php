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

namespace Viserio\Component\Container\Definition\Traits;

/**
 * @property array<string, bool> $changes
 *
 * @internal
 */
trait ClassAwareTrait
{
    /**
     * The class name of the object.
     *
     * @var string
     */
    protected $class;

    /**
     * {@inheritdoc}
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * {@inheritdoc}
     */
    public function setClass($class): void
    {
        $this->changes['class'] = true;

        $this->class = \is_object($class) ? \get_class($class) : $class;
    }
}
