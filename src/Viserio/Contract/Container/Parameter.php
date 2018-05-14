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

namespace Viserio\Contract\Container;

interface Parameter
{
    /**
     * Finds an parameter entry of the container by its identifier and returns it.
     *
     * @param string $id identifier of the entry to look for
     *
     * @throws \Viserio\Contract\Container\Exception\NotFoundException
     *
     * @return mixed
     */
    public function getParameter(string $id);

    /**
     * Returns true if the container can return an parameter entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id identifier of the entry to look for
     *
     * @return bool
     */
    public function hasParameter(string $id): bool;
}
