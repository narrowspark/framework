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

namespace Viserio\Component\Container\PhpParser;

use PhpParser\ErrorHandler;
use PhpParser\Node;
use PhpParser\Parser;
use function hash;

/**
 * @internal
 */
final class MemoizingParser implements Parser
{
    /**
     * Indexed by source hash.
     *
     * @var Node\Stmt[][]|null[]
     */
    private $sourceHashToAst = [];

    /**
     * A php parser instance.
     *
     * @var \PhpParser\Parser
     */
    private $wrappedParser;

    /**
     * Create a new MemoizingParser instance.
     *
     * @param \PhpParser\Parser $wrappedParser
     */
    public function __construct(Parser $wrappedParser)
    {
        $this->wrappedParser = $wrappedParser;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $code, ?ErrorHandler $errorHandler = null): ?array
    {
        // note: this code is mathematically buggy by default, as we are using a hash to identify
        //       cache entries. The string length is added to further reduce likeliness (although
        //       already imperceptible) of key collisions.
        //       In the "real world", this code will work just fine.
        $hash = \hash('sha256', $code) . ':' . \strlen($code);

        if (\array_key_exists($hash, $this->sourceHashToAst)) {
            return $this->sourceHashToAst[$hash];
        }

        return $this->sourceHashToAst[$hash] = $this->wrappedParser->parse($code, $errorHandler);
    }
}
