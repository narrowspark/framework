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

use PhpParser\Node as AstNode;
use PhpParser\Node\Expr\Variable as VariableNode;
use PhpParser\NodeVisitorAbstract as NodeVisitor;

/**
 * Detects if the closure's AST contains a $this variable.
 *
 * @internal
 */
final class ThisDetectorVisitor extends NodeVisitor
{
    /** @var bool */
    public $detected = false;

    /**
     * {@inheritdoc}
     */
    public function leaveNode(AstNode $node): void
    {
        if ($node instanceof VariableNode) {
            if ($node->name === 'this') {
                $this->detected = true;
            }
        }
    }
}
