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

namespace Viserio\Component\Log\Tests\Fixture;

use Viserio\Contract\Support\Arrayable;

class ArrayableClass implements Arrayable
{
    public function toArray(): array
    {
        return [
            'message' => true,
        ];
    }
}
