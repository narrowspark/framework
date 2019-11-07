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

namespace Viserio\Component\Container\PhpParser\NodeVisitor;

use PhpParser\Node as AstNode;
use PhpParser\Node\Expr\Closure as ClosureNode;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\Namespace_ as NamespaceNode;
use PhpParser\Node\Stmt\Trait_ as TraitNode;
use PhpParser\NodeVisitorAbstract;
use ReflectionFunction;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * This is a visitor that extends the nikic/php-parser library and looks for a
 * closure node and its location.
 *
 * @internal
 */
final class ClosureLocatorVisitor extends NodeVisitorAbstract
{
    /**
     * A instance of PhpParser Closure.
     *
     * @var null|\PhpParser\Node\Expr\Closure
     */
    public $closureNode;

    /**
     * Closure location data.
     *
     * @var array
     */
    public $location;

    /**
     * Create a new ClosureNodeVisitor instance.
     *
     * @param ReflectionFunction $reflection
     */
    public function __construct(ReflectionFunction $reflection)
    {
        $this->location = [
            'class' => null,
            'directory' => \dirname($reflection->getFileName()),
            'file' => $reflection->getFileName(),
            'function' => $reflection->getName(),
            'line' => $reflection->getStartLine(),
            'method' => null,
            'namespace' => null,
            'trait' => null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function enterNode(AstNode $node): void
    {
        // Determine information about the closure's location
        if ($this->closureNode === null) {
            if ($node instanceof NamespaceNode) {
                $namespace = $node->name !== null
                    ? $node->name->toString()
                    : null;
                $this->location['namespace'] = $namespace;
            }

            if ($node instanceof TraitNode) {
                $this->location['trait'] = (string) $node->name;
                $this->location['class'] = null;
            } elseif ($node instanceof ClassNode) {
                $this->location['class'] = (string) $node->name;
                $this->location['trait'] = null;
            }
        }

        // Locate the node of the closure
        if ($node instanceof ClosureNode && $node->getAttribute('startLine') === $this->location['line']) {
            if ($this->closureNode !== null) {
                $line = $this->location['file'] . ':' . $node->getAttribute('startLine');

                throw new RuntimeException(\sprintf('Two closures were declared on the same line (%s) of code. Cannot determine which closure was the intended target.', $line));
            }

            $this->closureNode = $node;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(AstNode $node): void
    {
        // Determine information about the closure's location
        if ($this->closureNode === null) {
            if ($node instanceof NamespaceNode) {
                $this->location['namespace'] = null;
            }

            if ($node instanceof TraitNode) {
                $this->location['trait'] = null;
            } elseif ($node instanceof ClassNode) {
                $this->location['class'] = null;
            }
        }
    }
}
