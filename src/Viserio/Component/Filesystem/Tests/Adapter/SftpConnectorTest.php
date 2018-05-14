<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Filesystem\Tests\Adapter;

use League\Flysystem\Sftp\SftpAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\SftpConnector;
use Viserio\Contract\OptionsResolver\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class SftpConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $connector = new SftpConnector([
            'host' => 'sftp.example.com',
            'port' => 22,
            'username' => 'your-username',
            'password' => 'your-password',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(SftpAdapter::class, $return);
    }

    public function testConnectWithoutPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The sftp connector requires [password] or [privateKey] configuration.');

        $connector = new SftpConnector([
            'host' => 'ftp.example.com',
            'port' => 21,
            'username' => 'your-username',
        ]);

        $connector->connect();
    }
}
