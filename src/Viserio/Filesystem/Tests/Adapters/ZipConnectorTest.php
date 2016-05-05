<?php
namespace Viserio\Filesystem\Tests\Adapters;

use Viserio\Filesystem\Adapters\ZipConnector;
use League\Flysystem\ZipArchive\ZipArchiveAdapter;

class ZipConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnectStandard()
    {
        $connector = new ZipConnector();

        $return = $connector->connect(['path' => __DIR__.'\stubs\test.zip']);

        $this->assertInstanceOf(ZipArchiveAdapter::class, $return);
    }

    public function testConnectWithPrefix()
    {
        $connector = new ZipConnector();

        $return = $connector->connect(['path' => __DIR__.'\stubs\test.zip', 'prefix' => 'your-prefix']);

        $this->assertInstanceOf(ZipArchiveAdapter::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The zip connector requires path configuration.
     */
    public function testConnectWithoutPath()
    {
        $connector = new ZipConnector();

        $connector->connect([]);
    }
}
