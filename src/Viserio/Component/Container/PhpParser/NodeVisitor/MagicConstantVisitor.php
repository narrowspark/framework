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
use PhpParser\Node\Scalar\LNumber as NumberNode;
use PhpParser\Node\Scalar\String_ as StringNode;
use PhpParser\NodeVisitorAbstract as NodeVisitor;

/**
 * This is a visitor that resolves magic constants (e.g., __FILE__) to their
 * intended values within a closure's AST.
 *
 * @internal
 */
final class MagicConstantVisitor extends NodeVisitor
{
    /** @var array */
    private $location;

    /**
     * Create a new MagicConstantVisitor instance.
     */
    public function __construct(array $location)
    {
        $this->location = $location;
    }

    /**
     * {@inheritdoc}
     */
    public function leaveNode(AstNode $node)
    {
        switch ($node->getType()) {
            case 'Scalar_MagicConst_Class':
                return new StringNode($this->location['class'] ?: '');
            case 'Scalar_MagicConst_Dir':
                return new StringNode($this->location['directory'] ?: '');
            case 'Scalar_MagicConst_File':
                return new StringNode($this->location['file'] ?: '');
            case 'Scalar_MagicConst_Function':
                return new StringNode($this->location['function'] ?: '');
            case 'Scalar_MagicConst_Line':
                return new NumberNode($node->getAttribute('startLine') ?: 0);
            case 'Scalar_MagicConst_Method':
                return new StringNode($this->location['method'] ?: '');
            case 'Scalar_MagicConst_Namespace':
                return new StringNode($this->location['namespace'] ?: '');
            case 'Scalar_MagicConst_Trait':
                return new StringNode($this->location['trait'] ?: '');
        }
    }
}
