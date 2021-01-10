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

trait ChangesAwareTrait
{
    /**
     * All tracked changes.
     *
     * @var array<string, bool>
     */
    protected $changes = [];

    /**
     * {@inheritdoc}
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * {@inheritdoc}
     */
    public function setChanges(array $changes)
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setChange(string $key, bool $changed)
    {
        $this->changes[$key] = $changed;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChange(string $key): bool
    {
        return $this->changes[$key] ?? false;
    }
}
