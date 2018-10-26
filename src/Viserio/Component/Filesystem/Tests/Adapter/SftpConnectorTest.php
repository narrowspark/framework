<?php
declare(strict_types=1);
namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Sftp\SftpAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException;
use Viserio\Component\Filesystem\Adapter\SftpConnector;

/**
 * @internal
 */
final class SftpConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $connector = new SftpConnector([
            'host'     => 'sftp.example.com',
            'port'     => 22,
            'username' => 'your-username',
            'password' => 'your-password',
        ]);

        $return = $connector->connect();

        $this->assertInstanceOf(SftpAdapter::class, $return);
    }

    public function testConnectWithoutPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The sftp connector requires [password] or [privateKey] configuration.');

        $connector = new SftpConnector([
            'host'     => 'ftp.example.com',
            'port'     => 21,
            'username' => 'your-username',
        ]);

        $connector->connect();
    }
}
