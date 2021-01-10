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
        $sourceId,
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
     * @param int|string $id
     */
    private function createNode($id, $value): ServiceReferenceGraphNodeContract
    {
        if (\array_key_exists($id, $this->nodes) && $this->nodes[$id]->getValue() === $value) {
            return $this->nodes[$id];
        }

        return $this->nodes[$id] = new ServiceReferenceGraphNode($id, $value);
    }
}
