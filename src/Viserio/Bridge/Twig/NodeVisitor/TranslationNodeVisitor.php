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

namespace Viserio\Bridge\Twig\NodeVisitor;

use Twig\Environment;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Viserio\Bridge\Twig\Node\TransNode;

/**
 * TranslationNodeVisitor extracts translation messages.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class TranslationNodeVisitor extends AbstractNodeVisitor
{
    /** @var string */
    private const UNDEFINED_DOMAIN = '_undefined';

    /**
     * Enable/Disable status of node.
     *
     * @var bool
     */
    private $enabled = false;

    /**
     * Array of all given messages.
     *
     * @var array
     */
    private $messages = [];

    /**
     * Get a list of messages.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Enable node and clear messages.
     *
     * @return void
     */
    public function enable(): void
    {
        $this->enabled = true;
        $this->messages = [];
    }

    /**
     * Disable node and clear messages.
     *
     * @return void
     */
    public function disable(): void
    {
        $this->enabled = false;
        $this->messages = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function doEnterNode(Node $node, Environment $env): Node
    {
        if (! $this->enabled) {
            return $node;
        }

        if ($node instanceof FilterExpression
            && $node->getNode('node') instanceof ConstantExpression
            && $node->getNode('filter')->getAttribute('value') === 'trans'
        ) {
            // extract constant nodes with a trans filter
            $this->messages[] = [
                $node->getNode('node')->getAttribute('value'),
                $this->getReadDomainFromArguments($node->getNode('arguments'), 1),
            ];
        } elseif ($node instanceof TransNode) {
            // extract trans nodes
            $this->messages[] = [
                $node->getNode('body')->getAttribute('data'),
                $node->hasNode('domain') ? $this->getReadDomainFromNode($node->getNode('domain')) : null,
            ];
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    protected function doLeaveNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    /**
     * @param \Twig\Node\Node $arguments
     * @param int             $index
     *
     * @return null|string
     */
    private function getReadDomainFromArguments(Node $arguments, int $index): ?string
    {
        if ($arguments->hasNode('domain')) {
            $argument = $arguments->getNode('domain');
        } elseif ($arguments->hasNode($index)) {
            $argument = $arguments->getNode($index);
        } else {
            return null;
        }

        return $this->getReadDomainFromNode($argument);
    }

    /**
     * @param \Twig\Node\Node $node
     *
     * @return null|string
     */
    private function getReadDomainFromNode(Node $node): ?string
    {
        if ($node instanceof ConstantExpression) {
            return $node->getAttribute('value');
        }

        return self::UNDEFINED_DOMAIN;
    }
}
