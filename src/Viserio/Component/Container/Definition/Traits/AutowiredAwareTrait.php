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
