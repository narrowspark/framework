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
