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
