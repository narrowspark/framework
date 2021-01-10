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

namespace Viserio\Component\Container\Argument;

use Viserio\Component\Container\Argument\Traits\ReferenceSetArgumentTrait;
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;

final class ArrayArgument implements ArgumentContract
{
    use ReferenceSetArgumentTrait;
}
