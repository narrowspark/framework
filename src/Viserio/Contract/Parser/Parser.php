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

namespace Viserio\Contract\Parser;

interface Parser
{
    /**
     * Loads a file and output it content as array.
     *
     * @param string $payload The file content
     *
     * @throws \Viserio\Contract\Parser\Exception\ParseException
     *
     * @return array<int|string, mixed>
     */
    public function parse(string $payload): array;
}
