<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\TokenParsers;

use Twig_Token;
use Twig_TokenParser;
use Viserio\Bridge\Twig\Nodes\DumpNode;

/**
 * Token Parser for the 'dump' tag.
 *
 * Dump variables with:
 * <pre>
 *  {% dump %}
 *  {% dump foo %}
 *  {% dump foo, bar %}
 * </pre>
 *
 * @author Julien Galenski <julien.galenski@gmail.com>
 */
class DumpTokenParser extends Twig_TokenParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(Twig_Token $token)
    {
        $values = null;

        if (! $this->parser->getStream()->test(Twig_Token::BLOCK_END_TYPE)) {
            $values = $this->parser->getExpressionParser()->parseMultitargetExpression();
        }

        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new DumpNode($this->parser->getVarName(), $values, $token->getLine(), $this->getTag());
    }

    /**
     * {@inheritdoc}
     */
    public function getTag(): string
    {
        return 'dump';
    }
}
