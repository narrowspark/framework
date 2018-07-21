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
        $connector = new DropboxConnector([
            'token' => 'your-token',
        ]);

        $return = $connector->connect();

        static::assertInstanceOf(DropboxAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new DropboxConnector([
            'token'  => 'your-token',
            'prefix' => 'your-prefix',
        ]);

        $return = $connector->connect();

        static::assertInstanceOf(DropboxAdapter::class, $return);
    }
}
