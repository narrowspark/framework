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

namespace Viserio\Component\Container\Tests\Fixture\Make;

class MixedPrimitiveFixture
{
    public $first;

    public $last;

    public $stub;

    public function __construct($first, ImplementationFixture $stub, $last)
    {
        $this->stub = $stub;
        $this->last = $last;
        $this->first = $first;
    }
}
