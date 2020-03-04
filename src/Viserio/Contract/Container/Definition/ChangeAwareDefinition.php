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

namespace Viserio\Contract\Container\Definition;

interface ChangeAwareDefinition
{
    /**
     * Returns all changes tracked for the Definition object.
     *
     * @return array An array of changes for this Definition
     */
    public function getChanges(): array;

    /**
     * Sets the tracked changes for the Definition object.
     *
     * @param array $changes An array of changes for this Definition
     *
     * @return self
     */
    public function setChanges(array $changes);

    /**
     * Set or overwrite a tracked change.
     *
     * @return self
     */
    public function setChange(string $key, bool $changed);

    /**
     * Get a tracked change.
     */
    public function getChange(string $key): bool;
}
