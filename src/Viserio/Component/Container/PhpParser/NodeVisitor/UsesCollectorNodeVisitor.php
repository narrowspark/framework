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

namespace Viserio\Component\Container\PhpParser\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitorAbstract;

/**
 * Visitor to collect static variables in the method/function body and resolve them.
 */
// @todo add tests
class UsesCollectorNodeVisitor extends NodeVisitorAbstract
{
    /** @var null|Namespace_ */
    private $currentNamespace;

    public function getUses(): array
    {
        if ($this->currentNamespace === null) {
            return [];
        }

        $uses = [\implode('\\', $this->currentNamespace->name->parts) => null];

        foreach ($this->currentNamespace->stmts as $statement) {
            if ($statement instanceof Use_ || $statement instanceof GroupUse) {
                $prefix = '';

                if ($statement instanceof GroupUse) {
                    $prefix = $statement->prefix;
                }

                foreach ($statement->uses as $use) {
                    $name = \implode('\\', $use->name->parts);
                    $alias = $use->alias;

                    if ($alias !== null) {
                        $alias = $alias->name;
                    }

                    if ($prefix !== '') {
                        $name = "{$prefix}\\{$name}";
                    }

                    $uses[$name] = $alias;
                }
            }
        }

        return $uses;
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node): void
    {
        if ($node instanceof Namespace_) {
            $this->currentNamespace = $node;
        }
    }
}
