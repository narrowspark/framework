<?php
declare(strict_types=1);
namespace Viserio\Filesystem\Tests\Adapters;

use League\Flysystem\Vfs\VfsAdapter;
use Viserio\Filesystem\Adapters\VfsConnector;
use PHPUnit\Framework\TestCase;

class VfsConnectorTest extends TestCase
{
    public function testConnectStandard()
    {
        $connector = new VfsConnector();

        $return = $connector->connect([]);

        self::assertInstanceOf(VfsAdapter::class, $return);
    }
}
