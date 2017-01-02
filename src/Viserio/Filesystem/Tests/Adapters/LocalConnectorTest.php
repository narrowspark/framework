<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\Adapter\Local;
use Viserio\Filesystem\Adapters\LocalConnector;
use PHPUnit\Framework\TestCase;

class LocalConnectorTest extends TestCase
{
    public function testConnectStandard()
    {
        $connector = new LocalConnector();

        $return = $connector->connect(['path' => __DIR__]);

        self::assertInstanceOf(Local::class, $return);
    }

    public function testConnectWithPrefix()
    {
        $connector = new LocalConnector();

        $return = $connector->connect(['path' => __DIR__, 'prefix' => 'your-prefix']);

        self::assertInstanceOf(Local::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The local connector requires path configuration.
     */
    public function testConnectWithoutPath()
    {
        $connector = new LocalConnector();

        $connector->connect([]);
    }
}
