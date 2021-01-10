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

use ArrayIterator;

class MockFileListIterator extends ArrayIterator
{
    public function __construct(array $filesArray = [])
    {
        $files = \array_map(function ($file) {
            return new MockSplFileInfo($file);
        }, $filesArray);

        parent::__construct($files);
    }
}
