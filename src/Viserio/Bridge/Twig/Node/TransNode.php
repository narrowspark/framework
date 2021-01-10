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

namespace Viserio\Bridge\Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\Node\TextNode;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class TransNode extends Node
{
    /**
     * Create a new trans node instance.
     *
     * @param \Twig\Node\Node $domain
     */
    public function __construct(
        Node $body,
        ?Node $domain = null,
        ?AbstractExpression $vars = null,
        ?AbstractExpression $locale = null,
        int $lineNumber = 0,
        ?string $tag = null
    ) {
        $nodes = ['body' => $body];

        if ($domain !== null) {
            $nodes['domain'] = $domain;
        }

        if ($vars !== null) {
            $nodes['vars'] = $vars;
        }

        if ($locale !== null) {
            $nodes['locale'] = $locale;
        }

        parent::__construct($nodes, [], $lineNumber, $tag);
    }

    /**
     * {@inheritdoc}
     */
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        /** @var \Twig\Node\Expression\ArrayExpression $defaults */
        $defaults = new ArrayExpression([], -1);
        $vars = null;

        if ($this->hasNode('vars') && ($vars = $this->getNode('vars')) instanceof ArrayExpression) {
            $defaults = $vars;
        }

        /** @var \Twig\Node\Expression\ArrayExpression $body */
        $body = $this->getNode('body');

        [$msg, $defaults] = $this->compileString($body, $defaults, (bool) $vars);

        $locale = null;

        if ($this->hasNode('locale')) {
            $locale = $this->getNode('locale');
        }

        $compiler->write('echo $this->env->getExtension(\'Viserio\Bridge\Twig\Extension\TranslatorExtension\')->getTranslator(');

        if ($locale === null) {
            $compiler->raw('null)->trans(');
        } else {
            $compiler->subcompile($locale)
                ->raw(')->trans(');
        }

        $compiler->subcompile($msg);

        $compiler->raw(', ');

        if ($vars !== null) {
            $compiler->raw('array_merge(')
                ->subcompile($defaults)
                ->raw(', ')
                ->subcompile($this->getNode('vars'))
                ->raw(')');
        } else {
            $compiler->subcompile($defaults);
        }

        $compiler->raw(', ');

        if (! $this->hasNode('domain')) {
            $compiler->repr('messages');
        } else {
            $compiler->subcompile($this->getNode('domain'));
        }

        $compiler->raw(");\n");
    }

    /**
     * Compile string with given variables.
     */
    protected function compileString(Node $body, ArrayExpression $vars, bool $ignoreStrictCheck = false): array
    {
        if ($body instanceof ConstantExpression) {
            $msg = $body->getAttribute('value');
        } elseif ($body instanceof TextNode) {
            $msg = $body->getAttribute('data');
        } else {
            return [$body, $vars];
        }

        \preg_match_all('/(?<!{){([^,}|^,]+)/', $msg, $matches);

        foreach ($matches[1] as $var) {
            $key = new ConstantExpression($var, $body->getTemplateLine());

            if (! $vars->hasElement($key)) {
                $varExpr = new NameExpression($var, $body->getTemplateLine());
                $varExpr->setAttribute('ignore_strict_check', $ignoreStrictCheck);

                $vars->addElement($varExpr, $key);
            }
        }

        return [new ConstantExpression(\trim($msg), $body->getTemplateLine()), $vars];
    }
}
