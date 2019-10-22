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
