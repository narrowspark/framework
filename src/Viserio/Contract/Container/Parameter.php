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

namespace Viserio\Contract\Container;

interface Parameter
{
    /**
     * Finds an parameter entry of the container by its identifier and returns it.
     *
     * @param string $id identifier of the entry to look for
     *
     * @throws \Viserio\Contract\Container\Exception\ParameterNotFoundException
     */
    public function getParameter(string $id);

    /**
     * Returns true if the container can return an parameter entry for the given identifier.
     * Returns false otherwise.
     *
     * @param string $id identifier of the entry to look for
     */
    public function hasParameter(string $id): bool;

    /**
     * Returns all static parameters.
     *
     * @return array<int|string, mixed>
     */
    public function getParameters(): array;
}
