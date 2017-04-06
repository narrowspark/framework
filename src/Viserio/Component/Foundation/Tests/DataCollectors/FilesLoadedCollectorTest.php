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

        static::assertSame(
            [
                'icon'  => 'ic_insert_drive_file_white_24px.svg',
                'label' => '',
                'value' => '0',
            ],
            $collector->getMenu()
        );
    }

    public function testGetPanel()
    {
        $collector = new FilesLoadedCollector(__DIR__);
        $collector->collect(
            $this->mock(ServerRequestInterface::class),
            $this->mock(ResponseInterface::class)
        );

        static::assertTrue(is_string($collector->getPanel()));
    }
}
