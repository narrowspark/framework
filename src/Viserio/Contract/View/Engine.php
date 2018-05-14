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

namespace Viserio\Contract\View;

interface Engine
{
    /**
     * Returns the engine names.
     *
     * @return array
     */
    public static function getDefaultNames(): array;

    /**
     * Get the evaluated contents of the view.
     *
     * @param array $fileInfo
     * @param array $data
     *
     * @return string
     */
    public function get(array $fileInfo, array $data = []): string;
}
