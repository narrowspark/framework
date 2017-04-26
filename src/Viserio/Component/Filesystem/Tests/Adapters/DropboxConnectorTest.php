<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapters;

use Spatie\FlysystemDropbox\DropboxAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapters\DropboxConnector;

class DropboxConnectorTest extends TestCase
{
    public function testConnectStandard()
    {
        $connector = new DropboxConnector();

        $return = $connector->connect([
            'token' => 'your-token',
        ]);

        self::assertInstanceOf(DropboxAdapter::class, $return);
    }

    public function testConnectWithPrefix()
    {
        $connector = new DropboxConnector();

        $return = $connector->connect([
            'token'  => 'your-token',
            'prefix' => 'your-prefix',
        ]);

        self::assertInstanceOf(DropboxAdapter::class, $return);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The dropbox connector requires authentication token.
     */
    public function testConnectWithoutSecret()
    {
        $connector = new DropboxConnector();

        $connector->connect(['test' => 'your-token']);
    }
}
