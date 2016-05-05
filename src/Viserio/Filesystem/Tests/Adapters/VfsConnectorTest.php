<?php
namespace Viserio\Filesystem\Tests\Adapters;

use Viserio\Filesystem\Adapters\VfsConnector;
use League\Flysystem\Vfs\VfsAdapter;

class VfsConnectorTest extends \PHPUnit_Framework_TestCase
{
    public function testConnectStandard()
    {
        $connector = new VfsConnector();

        $return = $connector->connect([]);

        $this->assertInstanceOf(VfsAdapter::class, $return);
    }
}
