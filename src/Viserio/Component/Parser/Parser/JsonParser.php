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

namespace Viserio\Component\Parser\Parser;

use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Parser as ParserContract;

class JsonParser implements ParserContract
{
    /**
     * User specified recursion depth.
     *
     * @var int
     */
    private $depth = 512;

    /**
     * Bitmask of JSON decode options.
     *
     * @var int
     */
    private $options = 0;

    /**
     * Set the user specified recursion depth.
     *
     * @param int $depth
     *
     * @return void
     */
    public function setDepth(int $depth): void
    {
        $this->depth = $depth;
    }

    /**
     * Set the user specified recursion depth.
     *
     * @param int $options
     *
     * @return void
     */
    public function setOptions(int $options): void
    {
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        $json = \json_decode(\trim($payload), true, $this->depth, $this->options);

        if (\json_last_error() !== \JSON_ERROR_NONE) {
            throw new ParseException(['message' => \json_last_error_msg() . '.', 'code' => \json_last_error(), 'file' => $payload]);
        }

        return $json;
    }
}
