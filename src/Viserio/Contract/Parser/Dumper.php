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

interface Dumper
{
    /**
     * Dumps a array into a string.
     *
     * @param array<int|string, mixed> $data
     *
     * @throws \Viserio\Contract\Parser\Exception\DumpException If dumping fails on some formats
     */
    public function dump(array $data): string;
}
