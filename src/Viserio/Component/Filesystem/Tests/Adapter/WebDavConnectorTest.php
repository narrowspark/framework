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

use League\Flysystem\WebDAV\WebDAVAdapter;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Filesystem\Adapter\WebDavConnector;

/**
 * @internal
 *
 * @small
 */
final class WebDavConnectorTest extends TestCase
{
    public function testConnect(): void
    {
        $connector = new WebDavConnector([
            'auth' => [
                'baseUri' => 'http://example.org/dav/',
            ],
            'userName' => 'your-username',
            'password' => 'your-password',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(WebDAVAdapter::class, $return);
    }

    public function testConnectWithPrefix(): void
    {
        $connector = new WebDavConnector([
            'auth' => [
                'baseUri' => 'http://example.org/dav/',
            ],
            'userName' => 'your-username',
            'password' => 'your-password',
            'prefix' => 'your-prefix',
        ]);

        $return = $connector->connect();

        self::assertInstanceOf(WebDAVAdapter::class, $return);
    }
}
