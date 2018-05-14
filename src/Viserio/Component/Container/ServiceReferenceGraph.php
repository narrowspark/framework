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

namespace Viserio\Component\Container;

use Viserio\Contract\Container\Exception\InvalidArgumentException;
use Viserio\Contract\Container\ServiceReferenceGraph as ServiceReferenceGraphContract;
use Viserio\Contract\Container\ServiceReferenceGraphNode as ServiceReferenceGraphNodeContract;

final class ServiceReferenceGraph implements ServiceReferenceGraphContract
{
    /**
     * List of all nodes.
     *
     * @var \Viserio\Contract\Container\ServiceReferenceGraphNode[]
     */
    private $nodes = [];

    /**
     * {@inheritdoc}
     */
    public function getNodes(): array
    {
        return $this->nodes;
    }

    /**
     * {@inheritdoc}
     */
    public function hasNode(string $id): bool
    {
        return \array_key_exists($id, $this->nodes);
    }

    /**
     * {@inheritdoc}
     */
    public function getNode(string $id): ServiceReferenceGraphNodeContract
    {
        if (! \array_key_exists($id, $this->nodes)) {
            throw new InvalidArgumentException(\sprintf('There is no node with id [%s].', $id));
        }

        return $this->nodes[$id];
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        foreach ($this->nodes as $node) {
            $node->reset();
        }

        $this->nodes = [];
    }

    /**
     * {@inheritdoc}
     */
    public function connect(
        ?string $sourceId,
        $sourceValue,
        ?string $destId,
        $destValue = null,
        $reference = null,
        bool $lazy = false,
        bool $weak = false,
        bool $byConstructor = false
    ): void {
        if (null === $sourceId || null === $destId) {
            return;
        }

        $sourceNode = $this->createNode($sourceId, $sourceValue);
        $destNode = $this->createNode($destId, $destValue);

        $edge = new ServiceReferenceGraphEdge($sourceNode, $destNode, $reference, $lazy, $weak, $byConstructor);

        $sourceNode->addOutEdge($edge);
        $destNode->addInEdge($edge);
    }

    /**
     * Create a new node if the value is not the same.
     *
     * @param string $id
     * @param mixed  $value
     *
     * @return \Viserio\Contract\Container\ServiceReferenceGraphNode
     */
    private function createNode(string $id, $value): ServiceReferenceGraphNodeContract
    {
        if (\array_key_exists($id, $this->nodes) && $this->nodes[$id]->getValue() === $value) {
            return $this->nodes[$id];
        }

        return $this->nodes[$id] = new ServiceReferenceGraphNode($id, $value);
    }
}
