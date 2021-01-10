<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Parser\Parser;

use Throwable;
use Viserio\Contract\Parser\Exception\ParseException;
use Viserio\Contract\Parser\Parser as ParserContract;

class PhpArrayParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        if (! \file_exists($payload)) {
            throw new ParseException(\sprintf('No such file [%s] found.', $payload));
        }

        try {
            $data = require $payload;
        } catch (Throwable $exception) {
            throw ParseException::createFromException(\sprintf('An exception was thrown by file [%s].', $payload), $exception);
        }

        return (array) $data;
    }
}
