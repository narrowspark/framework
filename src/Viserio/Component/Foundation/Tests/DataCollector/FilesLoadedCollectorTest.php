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

namespace Viserio\Component\Foundation\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Foundation\DataCollector\FilesLoadedCollector;

/**
 * @internal
 *
 * @small
 */
final class FilesLoadedCollectorTest extends MockeryTestCase
{
    public function testGetMenu(): void
    {
        $collector = new FilesLoadedCollector(__DIR__);

        self::assertSame(
            [
                'icon' => 'ic_insert_drive_file_white_24px.svg',
                'label' => '',
                'value' => '0',
            ],
            $collector->getMenu()
        );
    }
}
