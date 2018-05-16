<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TransDefaultDomainNode extends Node
{
    /**
     * {@inheritdoc}
     */
    public function __construct(AbstractExpression $expr, $lineno = 0, $tag = null)
    {
        parent::__construct(['expr' => $expr], [], $lineno, $tag);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function compile(Compiler $compiler): void
    {
        // noop as this node is just a marker for TranslationDefaultDomainNodeVisitor
    }
}
