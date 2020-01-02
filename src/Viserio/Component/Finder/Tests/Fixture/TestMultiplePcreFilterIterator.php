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

namespace Viserio\Component\Finder\Tests\Fixture;

use BadFunctionCallException;
use Viserio\Component\Finder\Filter\AbstractMultiplePcreFilterIterator;

class TestMultiplePcreFilterIterator extends AbstractMultiplePcreFilterIterator
{
    public function __construct()
    {
    }

    public function accept(): void
    {
        throw new BadFunctionCallException('Not implemented.');
    }

    public function isRegex(string $str): bool
    {
        return parent::isRegex($str);
    }

    public function toRegex(string $str): string
    {
        throw new BadFunctionCallException('Not implemented.');
    }
}
