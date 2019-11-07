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
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_ as ClassNode;
use PhpParser\Node\Stmt\Namespace_ as NamespaceNode;
use PhpParser\Node\Stmt\Trait_ as TraitNode;
use PhpParser\NodeVisitorAbstract;
use ReflectionClass;
use Viserio\Contract\Container\Exception\RuntimeException;

/**
 * This is a visitor that extends the nikic/php-parser library and looks for a
 * anonymous class objects node and its location.
 *
 * @internal
 */
final class AnonymousClassLocatorVisitor extends NodeVisitorAbstract
{
    /** @var array */
    public $location;

    /** @var null|\PhpParser\Node\Expr\New_ */
    public $anonymousClassNode;

    /** @var ReflectionClass */
    private $reflection;

    /**
     * Create a new AnonymousClassLocatorVisitor instance.
     *
     * @param ReflectionClass $reflection
     */
    public function __construct(ReflectionClass $reflection)
    {
        $this->reflection = $reflection;
        $this->location = [
            'class' => null,
            'directory' => \dirname($this->reflection->getFileName()),
            'file' => $this->reflection->getFileName(),
            'function' => $this->reflection->getName(),
            'line' => $this->reflection->getStartLine(),
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
        if ($this->anonymousClassNode === null) {
            if ($node instanceof NamespaceNode) {
                $this->location['namespace'] = $node->name !== null ? (string) $node->name : null;
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
        if ($node instanceof New_ && $node->class instanceof ClassNode && $node->class->name === null && $node->getAttribute('startLine') === $this->location['line']) {
            if ($this->anonymousClassNode !== null) {
                $line = $this->location['file'] . ':' . $node->getAttribute('startLine');

                throw new RuntimeException(\sprintf('Two anonymous classes were declared on the same line (%s) of code. Cannot determine which anonymous class was the intended target.', $line));
            }

            $this->anonymousClassNode = $node;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(AstNode $node): void
    {
        // Determine information about the closure's location
        if ($this->anonymousClassNode === null) {
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
