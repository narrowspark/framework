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

namespace Viserio\Component\Bus\Tests\Fixture;

class BusDispatcherSetCommand
{
    private $value = 'bar';

    public function set($value = '')
    {
        $this->value = $value;

        return $this;
    }

    public function handle()
    {
        return $this->value;
    }
}
