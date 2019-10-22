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
