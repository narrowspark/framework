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
     *
     * @var mixed
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
     *
     * @param \Viserio\Contract\Container\ServiceReferenceGraphNode $sourceNode
     * @param \Viserio\Contract\Container\ServiceReferenceGraphNode $destNode
     * @param mixed                                                 $value
     * @param bool                                                  $lazy
     * @param bool                                                  $weak
     * @param bool                                                  $byConstructor
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
