<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\NodeVisitor;

use Symfony\Bridge\Twig\Node\TransNode;
use Twig\Environment;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * TranslationNodeVisitor extracts translation messages.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class TranslationNodeVisitor extends AbstractNodeVisitor
{
    private const UNDEFINED_DOMAIN = '_undefined';

    private $enabled = false;

    private $messages = [];

    public function enable()
    {
        $this->enabled  = true;
        $this->messages = [];
    }

    public function disable()
    {
        $this->enabled  = false;
        $this->messages = [];
    }

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
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function doEnterNode(Node $node, Environment $env)
    {
        if (! $this->enabled) {
            return $node;
        }

        if (
            $node instanceof FilterExpression &&
            'trans' === $node->getNode('filter')->getAttribute('value') &&
            $node->getNode('node') instanceof ConstantExpression
        ) {
            // extract constant nodes with a trans filter
            $this->messages[] = [
                $node->getNode('node')->getAttribute('value'),
                $this->getReadDomainFromArguments($node->getNode('arguments'), 1),
            ];
        } elseif (
            $node instanceof FilterExpression &&
            'transchoice' === $node->getNode('filter')->getAttribute('value') &&
            $node->getNode('node') instanceof ConstantExpression
        ) {
            // extract constant nodes with a trans filter
            $this->messages[] = [
                $node->getNode('node')->getAttribute('value'),
                $this->getReadDomainFromArguments($node->getNode('arguments'), 2),
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
    protected function doLeaveNode(Node $node, Environment $env)
    {
        return $node;
    }

    /**
     * @param \Twig\Node\Node $arguments
     * @param int             $index
     *
     * @return string|null
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
     * @return string|null
     */
    private function getReadDomainFromNode(Node $node): ?string
    {
        if ($node instanceof ConstantExpression) {
            return $node->getAttribute('value');
        }

        return self::UNDEFINED_DOMAIN;
    }
}
