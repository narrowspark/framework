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

use Viserio\Contract\Container\ServiceReferenceGraphEdge as ServiceReferenceGraphEdgeContract;
use Viserio\Contract\Container\ServiceReferenceGraphNode as ServiceReferenceGraphNodeContract;

final class ServiceReferenceGraphEdge implements ServiceReferenceGraphEdgeContract
{
    /**
     * A ServiceReferenceGraphNode source instance.
     *
     * @var \Viserio\Contract\Container\ServiceReferenceGraphNode
     */
    private $sourceNode;

    /**
     * A ServiceReferenceGraphNode destination instance.
     *
     * @var \Viserio\Contract\Container\ServiceReferenceGraphNode
     */
    private $destNode;

    /**
     * The value of the edge.
     */
    private $value;

    /**
     * Is edge lazy.
     *
     * @var bool
     */
    private $lazy;

    /** @var bool */
    private $byConstructor;

    /** @var bool */
    private $weak;

    /**
     * Create a new DependencyGraphEdge instance.
     */
    public function __construct(
        ServiceReferenceGraphNodeContract $sourceNode,
        ServiceReferenceGraphNodeContract $destNode,
        $value = null,
        bool $lazy = false,
        bool $weak = false,
        bool $byConstructor = false
    ) {
        $this->sourceNode = $sourceNode;
        $this->destNode = $destNode;
        $this->value = $value;
        $this->lazy = $lazy;
        $this->weak = $weak;
        $this->byConstructor = $byConstructor;
    }

    /**
     * {@inheritdoc}
     */
    public function getSourceNode(): ServiceReferenceGraphNodeContract
    {
        return $this->sourceNode;
    }

    /**
     * {@inheritdoc}
     */
    public function getDestNode(): ServiceReferenceGraphNodeContract
    {
        return $this->destNode;
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
    public function isLazy(): bool
    {
        return $this->lazy;
    }

    /**
     * {@inheritdoc}
     */
    public function isWeak(): bool
    {
        return $this->weak;
    }

    /**
     * {@inheritdoc}
     */
    public function isReferencedByConstructor(): bool
    {
        return $this->byConstructor;
    }
}
