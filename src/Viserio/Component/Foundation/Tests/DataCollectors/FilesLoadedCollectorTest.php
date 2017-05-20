<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\DataCollectors;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Foundation\DataCollectors\FilesLoadedCollector;

class FilesLoadedCollectorTest extends MockeryTestCase
{
    public function testGetMenu()
    {
        $collector = new FilesLoadedCollector(__DIR__);

        self::assertSame(
            [
                'icon'  => 'ic_insert_drive_file_white_24px.svg',
                'label' => '',
                'value' => '0',
            ],
            $collector->getMenu()
        );
    }
}
