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
        $connector = new LocalConnector(['path' => __DIR__]);

        $return = $connector->connect();

        $this->assertInstanceOf(Local::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new LocalConnector(['path' => __DIR__, 'prefix' => 'your-prefix']);

        $return = $connector->connect();

        $this->assertInstanceOf(Local::class, $return);
    }
}
