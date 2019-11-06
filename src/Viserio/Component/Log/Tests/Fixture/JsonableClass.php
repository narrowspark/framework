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

use Viserio\Contract\Support\Jsonable;

class JsonableClass implements Jsonable
{
    public function toJson(int $options = 0): string
    {
        return \json_encode([
            'message' => true,
        ], \JSON_PRETTY_PRINT);
    }
}
