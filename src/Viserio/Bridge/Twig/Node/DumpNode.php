<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Node;

use Symfony\Component\VarDumper\VarDumper;
use Twig\Compiler;
use Twig\Node\Node;

/**
 * @author Julien Galenski <julien.galenski@gmail.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class DumpNode extends Node
{
    /**
     * @var string
     */
    private $varPrefix;

    /**
     * Create a new dump node instance.
     *
     * @param string               $varPrefix
     * @param \Twig\Node\Node|null $values
     * @param int                  $lineno
     * @param string|null          $tag
     */
    public function __construct(string $varPrefix, ?Node $values = null, int $lineno = 0, ?string $tag = null)
    {
        $nodes = [];

        if ($values !== null) {
            $nodes['values'] = $values;
        }

        parent::__construct($nodes, [], $lineno, $tag);

        $this->varPrefix = $varPrefix;
    }

    /**
     * {@inheritdoc}
     */
    public function compile(Compiler $compiler)
    {
        $compiler->write("if (\$this->env->isDebug()) {\n")
            ->indent();

        if (! $this->hasNode('values')) {
            // remove embedded templates (macros) from the context
            $compiler->write(sprintf('$%svars = [];' . "\n", $this->varPrefix))
                ->write(sprintf('foreach ($context as $%1$skey => $%1$sval) {' . "\n", $this->varPrefix))
                ->indent()
                ->write(sprintf('if (!$%sval instanceof \Twig\Template) {' . "\n", $this->varPrefix))
                ->indent()
                ->write(sprintf('$%1$svars[$%1$skey] = $%1$sval;' . "\n", $this->varPrefix))
                ->outdent()
                ->write("}\n")
                ->outdent()
                ->write("}\n")
                ->addDebugInfo($this)
                ->write(sprintf(VarDumper::class . '::dump($%svars);' . "\n", $this->varPrefix));
        } elseif (($values = $this->getNode('values')) && 1 === $values->count()) {
            $compiler->addDebugInfo($this)
                ->write(VarDumper::class . '::dump(')
                ->subcompile($values->getNode(0))
                ->raw(");\n");
        } else {
            $compiler->addDebugInfo($this)
                ->write(VarDumper::class . '::dump(array(' . "\n")
                ->indent();
            foreach ($values as $node) {
                $compiler->write('');

                if ($node->hasAttribute('name')) {
                    $compiler->string($node->getAttribute('name'))
                        ->raw(' => ');
                }

                $compiler->subcompile($node)
                    ->raw(",\n");
            }

            $compiler->outdent()
                ->write("));\n");
        }

        $compiler->outdent()
            ->write("}\n");
    }
}
