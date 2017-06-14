<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\TokenParser;

use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
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
class DumpTokenParser extends AbstractTokenParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(Token $token)
    {
        $values = null;

        if (! $this->parser->getStream()->test(Token::BLOCK_END_TYPE)) {
            $values = $this->parser->getExpressionParser()->parseMultitargetExpression();
        }

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

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
