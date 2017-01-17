<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapters;

use League\Flysystem\Vfs\VfsAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapters\VfsConnector;

class VfsConnectorTest extends TestCase
{
    public function testConnectStandard()
    {
        $connector = new VfsConnector();

        $return = $connector->connect([]);

        self::assertInstanceOf(VfsAdapter::class, $return);
    }
}
