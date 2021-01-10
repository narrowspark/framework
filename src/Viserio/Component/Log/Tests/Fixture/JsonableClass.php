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
