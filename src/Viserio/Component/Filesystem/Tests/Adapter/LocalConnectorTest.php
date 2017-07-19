<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\LocalConnector;

class LocalConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new LocalConnector();

        $return = $connector->connect(['path' => __DIR__]);

        self::assertInstanceOf(Local::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new LocalConnector();

        $return = $connector->connect(['path' => __DIR__, 'prefix' => 'your-prefix']);

        self::assertInstanceOf(Local::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The local connector requires path configuration.
     */
    public function testConnectWithoutPath(): void
    {
        $connector = new LocalConnector();

        $connector->connect([]);
    }
}
