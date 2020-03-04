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

namespace Viserio\Contract\View;

interface Engine
{
    /**
     * Returns the engine names.
     */
    public static function getDefaultNames(): array;

    /**
     * Get the evaluated contents of the view.
     */
    public function get(array $fileInfo, array $data = []): string;
}
