<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\NodeVisitor;

use Twig\Environment;
use Twig\Node\BlockNode;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\AssignNameExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FilterExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\Node\SetNode;
use Twig\NodeVisitor\AbstractNodeVisitor;
use Viserio\Bridge\Twig\Node\TransDefaultDomainNode;
use Viserio\Bridge\Twig\Node\TransNode;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TranslationDefaultDomainNodeVisitor extends AbstractNodeVisitor
{
    /**
     * @var Scope
     */
    private $scope;

    /**
     * Create a new TranslationDefaultDomainNodeVisitor instance.
     */
    public function __construct()
    {
        $this->scope = new Scope();
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority()
    {
        return -10;
    }

    /**
     * {@inheritdoc}
     */
    protected function doEnterNode(Node $node, Environment $env)
    {
        if ($node instanceof BlockNode || $node instanceof ModuleNode) {
            $this->scope = $this->scope->enter();
        }

        if ($node instanceof TransDefaultDomainNode) {
            if ($node->getNode('expr') instanceof ConstantExpression) {
                $this->scope->set('domain', $node->getNode('expr'));

                return $node;
            }

            $var  = $this->getVarName();
            $name = new AssignNameExpression($var, $node->getTemplateLine());
            $this->scope->set('domain', new NameExpression($var, $node->getTemplateLine()));

            return new SetNode(false, new Node([$name]), new Node([$node->getNode('expr')]), $node->getTemplateLine());
        }

        if (! $this->scope->has('domain')) {
            return $node;
        }

        if ($node instanceof FilterExpression && \in_array($node->getNode('filter')->getAttribute('value'), ['trans', 'transchoice'], true)) {
            $arguments = $node->getNode('arguments');
            $ind       = 'trans' === $node->getNode('filter')->getAttribute('value') ? 1 : 2;

            if ($this->isNamedArguments($arguments)) {
                if (! $arguments->hasNode('domain') && ! $arguments->hasNode($ind)) {
                    $arguments->setNode('domain', $this->scope->get('domain'));
                }
            } else {
                if (! $arguments->hasNode($ind)) {
                    if (! $arguments->hasNode($ind - 1)) {
                        $arguments->setNode($ind - 1, new ArrayExpression([], $node->getTemplateLine()));
                    }

                    $arguments->setNode($ind, $this->scope->get('domain'));
                }
            }
        } elseif ($node instanceof TransNode) {
            if (! $node->hasNode('domain')) {
                $node->setNode('domain', $this->scope->get('domain'));
            }
        }

        return $node;
    }

    /**
     * {@inheritdoc}
     */
    protected function doLeaveNode(Node $node, Environment $env)
    {
        if ($node instanceof TransDefaultDomainNode) {
            return false;
        }

        if ($node instanceof BlockNode || $node instanceof ModuleNode) {
            $this->scope = $this->scope->leave();
        }

        return $node;
    }

    /**
     * @param mixed $arguments
     *
     * @return bool
     */
    private function isNamedArguments($arguments): bool
    {
        foreach ($arguments as $name => $node) {
            if (! \is_int($name)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    private function getVarName(): string
    {
        return \sprintf('__internal_%s', \hash('sha256', \uniqid(\mt_rand(), true), false));
    }
}
