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

namespace Viserio\Contract\Exception;

use Throwable;

interface Transformer
{
    /**
     * Transform the provided exception.
     *
     * @param Throwable $exception
     *
     * @return Throwable
     */
    public function transform(Throwable $exception): Throwable;
}
