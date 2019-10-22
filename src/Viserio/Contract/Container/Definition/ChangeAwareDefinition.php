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
     * @param string $key
     * @param bool   $changed
     *
     * @return self
     */
    public function setChange(string $key, bool $changed);

    /**
     * Get a tracked change.
     *
     * @param string $key
     *
     * @return bool
     */
    public function getChange(string $key): bool;
}
