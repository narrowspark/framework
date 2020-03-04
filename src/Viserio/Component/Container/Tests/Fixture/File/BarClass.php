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

namespace Viserio\Component\Container\Tests\Fixture\File;

class BarClass extends BazClass
{
    public $foo = 'foo';

    protected $baz;

    public function getBaz()
    {
        return $this->baz;
    }

    public function setBaz(BazClass $baz): void
    {
        $this->baz = $baz;
    }
}
