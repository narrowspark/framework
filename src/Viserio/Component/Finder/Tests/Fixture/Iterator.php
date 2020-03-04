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

use SplFileInfo;

class Iterator implements \Iterator
{
    protected $values = [];

    public function __construct(array $values = [])
    {
        foreach ($values as $value) {
            $this->attach(new SplFileInfo($value));
        }
        $this->rewind();
    }

    public function attach(SplFileInfo $fileinfo): void
    {
        $this->values[] = $fileinfo;
    }

    public function rewind(): void
    {
        \reset($this->values);
    }

    public function valid(): bool
    {
        return false !== $this->current();
    }

    public function next(): void
    {
        \next($this->values);
    }

    public function current()
    {
        return \current($this->values);
    }

    public function key()
    {
        return \key($this->values);
    }
}
