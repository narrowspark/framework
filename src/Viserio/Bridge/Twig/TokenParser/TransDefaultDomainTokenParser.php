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

namespace Viserio\Bridge\Twig\TokenParser;

use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;
use Viserio\Bridge\Twig\Node\TransDefaultDomainNode;

/**
 * Token Parser for the 'trans_default_domain' tag.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TransDefaultDomainTokenParser extends AbstractTokenParser
{
    /**
     * Parses a token and returns a node.
     *
     * @param Token $token
     *
     * @return Node
     */
    public function parse(Token $token): Node
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new TransDefaultDomainNode($expr, $token->getLine(), $this->getTag());
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag(): string
    {
        return 'trans_default_domain';
    }
}
