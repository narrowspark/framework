<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\TokenParser;

use Twig\Error\SyntaxError;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Node;
use Twig\Node\TextNode;
use Twig\Source;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use Viserio\Bridge\Twig\Node\TransNode;

/**
 * Token Parser for the 'trans' tag.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 */
class TransTokenParser extends AbstractTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param \Twig\Token $token
     *
     * @throws \Twig\Error\SyntaxError
     *
     * @return \Twig\Node\Node
     */
    public function parse(Token $token): Node
    {
        $lineno = $token->getLine();
        $stream = $this->parser->getStream();

        $vars   = new ArrayExpression([], $lineno);
        $domain = null;
        $locale = null;

        if ($stream->test('with')) {
            // {% trans with vars %}
            $stream->next();
            $vars = $this->parser->getExpressionParser()->parseExpression();
        }

        if ($stream->test('from')) {
            // {% trans from "messages" %}
            $stream->next();
            $domain = $this->parser->getExpressionParser()->parseExpression();
        }

        if ($stream->test('into')) {
            // {% trans into "fr" %}
            $stream->next();
            $locale = $this->parser->getExpressionParser()->parseExpression();
        }

        // {% trans %}message{% endtrans %}
        $stream->expect(Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse(static function ($token) {
            return $token->test(['endtrans']);
        }, true);

        if (! $body instanceof TextNode && ! $body instanceof AbstractExpression) {
            $name = $stream->getSourceContext()->getName();

            throw new SyntaxError(
                'A message inside a trans tag must be a simple text.',
                $body->getTemplateLine(),
                new Source(
                    $stream->__toString(),
                    $name
                )
            );
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        return new TransNode($body, $domain, $vars, $locale, $lineno, $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag(): string
    {
        return 'trans';
    }
}
