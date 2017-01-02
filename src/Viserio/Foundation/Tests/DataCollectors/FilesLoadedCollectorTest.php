<?php
declare(strict_types=1);
namespace Viserio\Foundation\Tests\DataCollectors;

use Mockery as Mock;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Foundation\DataCollectors\FilesLoadedCollector;
use PHPUnit\Framework\TestCase;

class FilesLoadedCollectorTest extends TestCase
{
    use MockeryTrait;

    public function tearDown()
    {
        parent::tearDown();

        $this->allowMockingNonExistentMethods(true);

        // Verify Mockery expectations.
        Mock::close();
    }

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
