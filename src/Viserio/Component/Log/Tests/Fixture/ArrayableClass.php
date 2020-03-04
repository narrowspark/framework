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
