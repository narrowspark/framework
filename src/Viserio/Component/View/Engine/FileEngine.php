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

namespace Viserio\Component\View\Engine;

use Viserio\Contract\View\Engine as EngineContract;

class FileEngine implements EngineContract
{
    /**
     * {@inheritdoc}
     */
    public static function getDefaultNames(): array
    {
        return ['file'];
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function get(array $fileInfo, array $data = []): string
    {
        return \file_get_contents($fileInfo['path']);
    }
}
