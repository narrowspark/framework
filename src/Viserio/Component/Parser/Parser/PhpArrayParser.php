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
            throw new ParseException(['message' => \sprintf('No such file [%s] found.', $payload)]);
        }

        try {
            $data = require $payload;
        } catch (Throwable $exception) {
            throw new ParseException(['message' => \sprintf('An exception was thrown by file [%s].', $payload), 'exception' => $exception]);
        }

        return (array) $data;
    }
}
