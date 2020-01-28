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
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasNode(string $id): bool;

    /**
     * Gets a node by identifier.
     *
     * @param string $id
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
     * @param mixed           $sourceValue
     * @param null|string     $destId
     * @param null|mixed      $destValue
     * @param null|mixed      $reference
     * @param bool            $lazy
     * @param bool            $weak
     * @param bool            $byConstructor
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
