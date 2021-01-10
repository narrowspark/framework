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

namespace Viserio\Component\Foundation\Tests\DataCollector;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Foundation\DataCollector\FilesLoadedCollector;

/**
 * @internal
 *
 * @small
 * @coversNothing
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
