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

namespace Viserio\Contract\Translation;

interface Extractor
{
    /**
     * Extracts translation messages from files, a file or a directory to a array.
     *
     * @param array|string $resource Files, a file or a directory
     *
     * @return array
     */
    public function extract($resource): array;

    /**
     * Sets the prefix that should be used for new found messages.
     *
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void;
}
