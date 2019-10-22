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
