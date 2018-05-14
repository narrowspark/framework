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
