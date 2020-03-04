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

namespace Viserio\Component\Container\Tests\Fixture\Make;

class DefaultValue
{
    public $stub;

    public $default;

    public function __construct(ImplementationFixture $stub, $default = 'narrowspark')
    {
        $this->stub = $stub;
        $this->default = $default;
    }
}
