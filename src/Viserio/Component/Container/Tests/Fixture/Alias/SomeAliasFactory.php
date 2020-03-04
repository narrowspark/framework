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

namespace Viserio\Component\Container\Tests\Fixture\Alias;

class SomeAliasFactory
{
    public $alias;

    public function __construct(AliasEnvFixture $alias)
    {
        $this->alias = $alias;
    }
}
