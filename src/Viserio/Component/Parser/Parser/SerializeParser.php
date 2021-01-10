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

class SerializeParser implements ParserContract
{
    /**
     * {@inheritdoc}
     */
    public function parse(string $payload): array
    {
        try {
            return \unserialize(\trim($payload), ['allowed_classes' => false]);
        } catch (Throwable $exception) {
            throw ParseException::createFromException(\sprintf('Failed to parse serialized Data; %s.', $exception->getMessage()), $exception);
        }
    }
}
