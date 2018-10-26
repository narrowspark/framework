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
        $connector = new ZipConnector(['path' => __DIR__ . '\stubs\test.zip']);

        $return = $connector->connect();

        $this->assertInstanceOf(ZipArchiveAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new ZipConnector(['path' => __DIR__ . '\stubs\test.zip', 'prefix' => 'your-prefix']);

        $return = $connector->connect();

        $this->assertInstanceOf(ZipArchiveAdapter::class, $return);
    }
}
