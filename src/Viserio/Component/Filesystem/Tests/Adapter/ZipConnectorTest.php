<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\ZipArchive\ZipArchiveAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\ZipConnector;

/**
 * @internal
 */
final class ZipConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new ZipConnector();

        $return = $connector->connect(['path' => __DIR__ . '\stubs\test.zip']);

        $this->assertInstanceOf(ZipArchiveAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new ZipConnector();

        $return = $connector->connect(['path' => __DIR__ . '\stubs\test.zip', 'prefix' => 'your-prefix']);

        $this->assertInstanceOf(ZipArchiveAdapter::class, $return);
    }

    public function testConnectWithoutPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The zip connector requires path configuration.');

        $connector = new ZipConnector();

        $connector->connect([]);
    }
}
