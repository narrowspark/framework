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
