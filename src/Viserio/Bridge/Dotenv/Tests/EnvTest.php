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

use Nyholm\NSA;
use PHPUnit\Framework\TestCase;
use Viserio\Bridge\Dotenv\Env;

/**
 * @internal
 * @covers \Viserio\Bridge\Dotenv\Env
 * @small
 */
final class EnvTest extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        \putenv('TEST_TRUE=');
        \putenv('TEST_FALSE=');
        \putenv('TEST_NULL=');
        \putenv('TEST_NUM=');
        \putenv('TEST_EMPTY=');
        \putenv('TEST_NORMAL=');
        \putenv('TEST_QUOTES=');
        \putenv('TEST_BASE64=');
        \putenv('foo=');
        \putenv('TEST_TRUE');
        \putenv('TEST_FALSE');
        \putenv('TEST_NULL');
        \putenv('TEST_NUM');
        \putenv('TEST_EMPTY');
        \putenv('TEST_NORMAL');
        \putenv('TEST_QUOTES');
        \putenv('TEST_BASE64');
        \putenv('foo');
    }

    public function testEnv(): void
    {
        \putenv('foo=bar');
        \putenv('TEST_NORMAL=teststring');

        self::assertEquals('bar', Env::get('foo'));
        self::assertSame('teststring', Env::get('TEST_NORMAL'));
    }

    public function testEnvWithQuotes(): void
    {
        \putenv('foo="bar"');
        \putenv('TEST_QUOTES="teststring"');

        self::assertEquals('bar', Env::get('foo'));
        self::assertSame('teststring', Env::get('TEST_QUOTES'));
    }

    public function testEnvTrue(): void
    {
        \putenv('foo=true');
        \putenv('TEST_TRUE=true');

        self::assertTrue(env('foo'));
        self::assertTrue(Env::get('TEST_TRUE'));

        \putenv('foo=(true)');
        \putenv('TEST_TRUE=(true)');

        self::assertTrue(env('foo'));
        self::assertTrue(Env::get('TEST_TRUE'));
    }

    public function testEnvFalse(): void
    {
        \putenv('foo=false');
        \putenv('TEST_FALSE=false');

        self::assertFalse(env('foo'));
        self::assertFalse(Env::get('TEST_FALSE'));

        \putenv('foo=(false)');
        \putenv('TEST_FALSE=(false)');

        self::assertFalse(env('foo'));
        self::assertFalse(Env::get('TEST_FALSE'));
    }

    public function testEnvEmpty(): void
    {
        \putenv('foo=');
        \putenv('TEST_EMPTY=');

        self::assertEquals('', Env::get('foo'));
        self::assertEquals('', Env::get('TEST_EMPTY'));

        \putenv('foo=empty');
        \putenv('TEST_EMPTY=empty');

        self::assertEquals('', Env::get('foo'));
        self::assertEquals('', Env::get('TEST_EMPTY'));

        \putenv('foo=(empty)');
        \putenv('TEST_EMPTY=(empty)');

        self::assertEquals('', Env::get('foo'));
        self::assertEquals('', Env::get('TEST_EMPTY'));
    }

    public function testEnvNull(): void
    {
        \putenv('foo=null');
        \putenv('TEST_NULL=null');

        self::assertEquals('', Env::get('foo'));
        self::assertEquals('', Env::get('TEST_NULL'));

        \putenv('foo=(null)');
        \putenv('TEST_NULL=(null)');

        self::assertEquals('', Env::get('foo'));
        self::assertEquals('', Env::get('TEST_NULL'));
    }

    public function testEnvWithNumber(): void
    {
        \putenv('foo=25');
        \putenv('TEST_NUM=25');

        self::assertEquals('25', Env::get('foo'));
        self::assertSame(25, Env::get('TEST_NUM'));
    }

    public function testEnvWithBase64(): void
    {
        \putenv('foo=base64:dGVzdA==');
        \putenv('TEST_BASE64=base64:dGVzdA==');

        self::assertEquals('test', Env::get('foo'));
        self::assertSame('test', Env::get('TEST_BASE64'));
    }

    public function testEnvWithNotSetValue(): void
    {
        self::assertFalse(Env::get('NOT_SET', false));
        self::assertSame('test', Env::get('NOT_SET', static function () {
            return 'test';
        }));
    }

    /**
     * @runInSeparateProcess
     */
    public function testEnableAndDisablePutenv(): void
    {
        Env::enablePutenv();

        self::assertTrue(NSA::getProperty(Env::class, 'putenv'));
        self::assertNull(NSA::getProperty(Env::class, 'factory'));
        self::assertNull(NSA::getProperty(Env::class, 'variables'));

        Env::disablePutenv();

        self::assertFalse(NSA::getProperty(Env::class, 'putenv'));
        self::assertNull(NSA::getProperty(Env::class, 'factory'));
        self::assertNull(NSA::getProperty(Env::class, 'variables'));
    }
}
