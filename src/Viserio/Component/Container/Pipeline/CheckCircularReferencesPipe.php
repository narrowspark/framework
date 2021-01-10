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

namespace Viserio\Component\Container\Pipeline;

use Viserio\Contract\Container\ContainerBuilder as ContainerBuilderContract;
use Viserio\Contract\Container\Exception\CircularDependencyException;
use Viserio\Contract\Container\Pipe as PipeContract;

/**
 * @internal
 */
final class CheckCircularReferencesPipe implements PipeContract
{
    /** @var array */
    private $currentPath = [];

    /** @var array */
    private $checkedNodes = [];

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilderContract $containerBuilder): void
    {
        $graph = $containerBuilder->getServiceReferenceGraph();

        foreach ($graph->getNodes() as $id => $node) {
            $this->currentPath = [$id];

            $this->checkOutEdges($node->getOutEdges());
        }
    }

    /**
     * Checks for circular references.
     *
     * @param \Viserio\Contract\Container\ServiceReferenceGraphEdge[] $edges An array of Edges
     *
     * @throws \Viserio\Contract\Container\Exception\CircularDependencyException when a circular reference is found
     */
    private function checkOutEdges(array $edges): void
    {
        foreach ($edges as $edge) {
            $node = $edge->getDestNode();
            $id = $node->getId();

            if (! \array_key_exists($id, $this->checkedNodes)) {
                $value = $node->getValue();

                // Don't check circular references for lazy edges
                if (($value === null || $value === '' || $value === false || (\is_array($value) && \count($value) === 0)) || (! $edge->isLazy() && ! $edge->isWeak())) {
                    $searchKey = \array_search($id, $this->currentPath, true);

                    $this->currentPath[] = $id;

                    if ($searchKey !== false) {
                        throw new CircularDependencyException($id, $this->currentPath);
                    }

                    $this->checkOutEdges($node->getOutEdges());
                }

                $this->checkedNodes[$id] = true;

                \array_pop($this->currentPath);
            }
        }
    }
}
