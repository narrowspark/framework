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

namespace Viserio\Contract\Parser;

interface Dumper
{
    /**
     * Dumps a array into a string.
     *
     * @param array<int|string, mixed> $data
     *
     * @throws \Viserio\Contract\Parser\Exception\DumpException If dumping fails on some formats
     *
     * @return string
     */
    public function dump(array $data): string;
}
