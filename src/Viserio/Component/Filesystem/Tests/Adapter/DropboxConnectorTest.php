<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use PHPUnit\Framework\TestCase;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Viserio\Component\Filesystem\Adapter\DropboxConnector;

/**
 * @internal
 */
final class DropboxConnectorTest extends TestCase
{
    public function testConnectStandard(): void
    {
        $connector = new DropboxConnector();

        $return = $connector->connect([
            'token' => 'your-token',
        ]);

        $this->assertInstanceOf(DropboxAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new DropboxConnector();

        $return = $connector->connect([
            'token'  => 'your-token',
            'prefix' => 'your-prefix',
        ]);

        $this->assertInstanceOf(DropboxAdapter::class, $return);
    }

    public function testConnectWithoutSecret(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The dropbox connector requires authentication token.');

        $connector = new DropboxConnector();

        $connector->connect(['test' => 'your-token']);
    }
}
