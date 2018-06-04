<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\LocalConnector;

/**
 * @internal
 */
final class LocalConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new LocalConnector();

        $return = $connector->connect(['path' => __DIR__]);

        $this->assertInstanceOf(Local::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new LocalConnector();

        $return = $connector->connect(['path' => __DIR__, 'prefix' => 'your-prefix']);

        $this->assertInstanceOf(Local::class, $return);
    }

    public function testConnectWithoutPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The local connector requires path configuration.');

        $connector = new LocalConnector();

        $connector->connect([]);
    }
}
