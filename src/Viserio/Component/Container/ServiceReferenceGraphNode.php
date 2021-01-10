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

use Viserio\Component\Container\Definition\AliasDefinition;
use Viserio\Contract\Container\Definition\Definition as DefinitionContract;
use Viserio\Contract\Container\ServiceReferenceGraphEdge as ServiceReferenceGraphEdgeContract;
use Viserio\Contract\Container\ServiceReferenceGraphNode as ServiceReferenceGraphNodeContract;

final class ServiceReferenceGraphNode implements ServiceReferenceGraphNodeContract
{
    /**
     * The node identifier.
     *
     * @var int|string
     */
    private $id;

    /**
     * List of DependencyGraphEdge in edges instances.
     *
     * @var \Viserio\Contract\Container\ServiceReferenceGraphEdge[]
     */
    private $inEdges = [];

    /**
     * List of DependencyGraphEdge out edges instances.
     *
     * @var \Viserio\Contract\Container\ServiceReferenceGraphEdge[]
     */
    private $outEdges = [];

    /**
     * The value of the node.
     */
    private $value;

    /**
     * Create a new ServiceReferenceGraphNode instance.
     *
     * @param int|string $id
     */
    public function __construct($id, $value)
    {
        $this->id = $id;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getInEdges(): array
    {
        return $this->inEdges;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutEdges(): array
    {
        return $this->outEdges;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function addInEdge(ServiceReferenceGraphEdgeContract $edge): void
    {
        $this->inEdges[] = $edge;
    }

    /**
     * {@inheritdoc}
     */
    public function addOutEdge(ServiceReferenceGraphEdgeContract $edge): void
    {
        $this->outEdges[] = $edge;
    }

    /**
     * {@inheritdoc}
     */
    public function isAlias(): bool
    {
        return $this->value instanceof AliasDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function isDefinition(): bool
    {
        return $this->value instanceof DefinitionContract;
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->inEdges = $this->outEdges = [];
    }
}
