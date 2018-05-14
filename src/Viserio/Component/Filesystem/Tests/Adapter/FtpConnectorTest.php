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

use League\Flysystem\Adapter\Ftp;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\FtpConnector;

/**
 * @internal
 *
 * @small
 */
final class FtpConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        if (! \defined('FTP_BINARY')) {
            self::markTestSkipped('The FTP_BINARY constant is not defined');
        }

        $connector = new FtpConnector([
            'host' => 'ftp.example.com',
            'port' => 21,
            'username' => 'your-username',
            'password' => 'your-password',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(Ftp::class, $return);
    }
}
