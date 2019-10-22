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
trait AutowiredAwareTrait
{
    /** @var bool */
    protected $autowired = true;

    /**
     * {@inheritdoc}
     */
    public function isAutowired(): bool
    {
        return $this->autowired;
    }

    /**
     * {@inheritdoc}
     */
    public function setAutowired(bool $autowired)
    {
        $this->changes['autowired'] = true;
        $this->autowired = $autowired;

        return $this;
    }
}
