<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\ZipConnector;

class ZipConnectorTest extends TestCase
{
    public function testConnectStandard()
    {
        $connector = new ZipConnector();

        $return = $connector->connect(['path' => __DIR__ . '\stubs\test.zip']);

        self::assertInstanceOf(ZipArchiveAdapter::class, $return);
    }

    public function testConnectWithPrefix()
    {
        $connector = new ZipConnector();

        $return = $connector->connect(['path' => __DIR__ . '\stubs\test.zip', 'prefix' => 'your-prefix']);

        self::assertInstanceOf(ZipArchiveAdapter::class, $return);
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
