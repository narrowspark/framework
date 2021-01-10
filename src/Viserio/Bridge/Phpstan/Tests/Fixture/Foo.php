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
