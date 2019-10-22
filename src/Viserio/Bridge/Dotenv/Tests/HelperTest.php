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

namespace Viserio\Bridge\Dotenv\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @small
 */
final class HelperTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        \putenv('TEST_NORMAL=');
        \putenv('foo=');
        \putenv('TEST_NORMAL');
        \putenv('foo');
    }

    public function testHelperFunction(): void
    {
        \putenv('foo=bar');
        \putenv('TEST_NORMAL=teststring');

        self::assertEquals('bar', env('foo'));
        self::assertSame('teststring', env('TEST_NORMAL'));
    }
}
