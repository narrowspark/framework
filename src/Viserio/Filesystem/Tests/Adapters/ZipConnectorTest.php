<?php

declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use Viserio\Filesystem\Adapters\ZipConnector;

class ZipConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnectStandard()
    {
        $connector = new ZipConnector();

        $return = $connector->connect(['path' => __DIR__ . '\stubs\test.zip']);

        $this->assertInstanceOf(ZipArchiveAdapter::class, $return);
    }

    public function testConnectWithPrefix()
    {
        $connector = new ZipConnector();

        $return = $connector->connect(['path' => __DIR__ . '\stubs\test.zip', 'prefix' => 'your-prefix']);

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
