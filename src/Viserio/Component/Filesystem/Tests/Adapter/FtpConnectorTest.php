<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Adapter\Ftp;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\FtpConnector;

/**
 * @internal
 */
final class FtpConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        if (! \defined('FTP_BINARY')) {
            static::markTestSkipped('The FTP_BINARY constant is not defined');
        }

        $connector = new FtpConnector([
            'host'     => 'ftp.example.com',
            'port'     => 21,
            'username' => 'your-username',
            'password' => 'your-password',
        ]);

        $return = $connector->connect();

        static::assertInstanceOf(Ftp::class, $return);
    }
}
