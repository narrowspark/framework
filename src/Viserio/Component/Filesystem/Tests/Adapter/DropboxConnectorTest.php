<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Viserio\Component\Filesystem\Adapter\DropboxConnector;

class DropboxConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new DropboxConnector();

        $return = $connector->connect([
            'token' => 'your-token',
        ]);

        self::assertInstanceOf(DropboxAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
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
    public function testConnectWithoutSecret(): void
    {
        $connector = new DropboxConnector();

        $connector->connect(['test' => 'your-token']);
    }
}
