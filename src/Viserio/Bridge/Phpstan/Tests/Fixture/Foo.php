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

namespace Viserio\Bridge\Phpstan\Tests\Fixture;

class Foo
{
    public function __invoke(): void
    {
        // TODO: Implement __invoke() method.
    }

    public function getFoo(): self
    {
        return $this;
    }

    public static function getStaticFoo(): self
    {
        return new static();
    }
}
