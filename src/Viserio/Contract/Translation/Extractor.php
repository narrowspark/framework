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

namespace Viserio\Contract\Translation;

interface Extractor
{
    /**
     * Extracts translation messages from files, a file or a directory to a array.
     *
     * @param array|string $resource Files, a file or a directory
     */
    public function extract($resource): array;

    /**
     * Sets the prefix that should be used for new found messages.
     */
    public function setPrefix(string $prefix): void;
}
