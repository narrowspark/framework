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

namespace Viserio\Component\Container\Argument;

use Viserio\Component\Container\Argument\Traits\ReferenceSetArgumentTrait;
use Viserio\Contract\Container\Argument\Argument as ArgumentContract;

final class IteratorArgument implements ArgumentContract
{
    use ReferenceSetArgumentTrait;
}
