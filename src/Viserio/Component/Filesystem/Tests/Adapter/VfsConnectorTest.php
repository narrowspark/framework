<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Vfs\VfsAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\VfsConnector;

/**
 * @internal
 */
final class VfsConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new VfsConnector();

        $return = $connector->connect();

        $this->assertInstanceOf(VfsAdapter::class, $return);
    }
}
