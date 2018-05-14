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

interface Parser
{
    /**
     * Loads a file and output it content as array.
     *
     * @param string $payload The file content
     *
     * @throws \Viserio\Contract\Parser\Exception\ParseException
     *
     * @return array
     */
    public function parse(string $payload): array;
}
