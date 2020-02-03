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
