<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapters;

use League\Flysystem\Dropbox\DropboxAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapters\DropboxConnector;

class DropboxConnectorTest extends TestCase
{
    public function testConnectStandard()
    {
        $connector = new DropboxConnector();

        $return = $connector->connect([
            'token' => 'your-token',
            'app'   => 'your-app',
        ]);

        self::assertInstanceOf(DropboxAdapter::class, $return);
    }

    public function testConnectWithPrefix()
    {
        $connector = new DropboxConnector();

        $return = $connector->connect([
            'token'  => 'your-token',
            'app'    => 'your-app',
            'prefix' => 'your-prefix',
        ]);

        self::assertInstanceOf(DropboxAdapter::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The dropbox connector requires authentication.
     */
    public function testConnectWithoutToken()
    {
        $connector = new DropboxConnector();

        $connector->connect(['app' => 'your-app']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The dropbox connector requires authentication.
     */
    public function testConnectWithoutSecret()
    {
        $connector = new DropboxConnector();

        $connector->connect(['token' => 'your-token']);
    }
}
