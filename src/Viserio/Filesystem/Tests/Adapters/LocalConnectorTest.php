<?php
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\Adapter\Local;
use Viserio\Filesystem\Adapters\LocalConnector;

class LocalConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnectStandard()
    {
        $connector = new LocalConnector();

        $return = $connector->connect(['path' => __DIR__]);

        $this->assertInstanceOf(Local::class, $return);
    }

    public function testConnectWithPrefix()
    {
        $connector = new LocalConnector();

        $return = $connector->connect(['path' => __DIR__, 'prefix' => 'your-prefix']);

        $this->assertInstanceOf(Local::class, $return);
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
