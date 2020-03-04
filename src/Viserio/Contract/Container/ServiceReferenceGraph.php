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

use Viserio\Contract\Support\Resettable as ResettableContract;

interface ServiceReferenceGraph extends ResettableContract
{
    /**
     * Returns all nodes.
     *
     * @return \Viserio\Contract\Container\ServiceReferenceGraphNode[]
     */
    public function getNodes(): array;

    /**
     * Returns true if the graph can return an entry for the given identifier.
     * Returns false otherwise.
     */
    public function hasNode(string $id): bool;

    /**
     * Gets a node by identifier.
     *
     *@throws \Viserio\Contract\Container\Exception\InvalidArgumentException if no node matches the supplied identifier
     *
     * @return \Viserio\Contract\Container\ServiceReferenceGraphNode
     */
    public function getNode(string $id): ServiceReferenceGraphNode;

    /**
     * Connects 2 nodes together in the Graph.
     *
     * @param null|int|string $sourceId
     * @param null|mixed      $destValue
     * @param null|mixed      $reference
     */
    public function connect(
        $sourceId,
        $sourceValue,
        ?string $destId,
        $destValue = null,
        $reference = null,
        bool $lazy = false,
        bool $weak = false,
        bool $byConstructor = false
    ): void;
}
