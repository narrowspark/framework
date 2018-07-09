<?php
declare(strict_types=1);
namespace Viserio\Component\Support\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Support\Env;

/**
 * @internal
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

        static::assertEquals('bar', Env::get('foo'));
        static::assertSame('teststring', Env::get('TEST_NORMAL'));
    }

    public function testEnvWithQuotes(): void
    {
        \putenv('foo="bar"');
        \putenv('TEST_QUOTES="teststring"');

        static::assertEquals('bar', Env::get('foo'));
        static::assertSame('teststring', Env::get('TEST_QUOTES'));
    }

    public function testEnvTrue(): void
    {
        \putenv('foo=true');
        \putenv('TEST_TRUE=true');

        static::assertTrue(env('foo'));
        static::assertTrue(Env::get('TEST_TRUE'));

        \putenv('foo=(true)');
        \putenv('TEST_TRUE=(true)');

        static::assertTrue(env('foo'));
        static::assertTrue(Env::get('TEST_TRUE'));
    }

    public function testEnvFalse(): void
    {
        \putenv('foo=false');
        \putenv('TEST_FALSE=false');

        static::assertFalse(env('foo'));
        static::assertFalse(Env::get('TEST_FALSE'));

        \putenv('foo=(false)');
        \putenv('TEST_FALSE=(false)');

        static::assertFalse(env('foo'));
        static::assertFalse(Env::get('TEST_FALSE'));
    }

    public function testEnvEmpty(): void
    {
        \putenv('foo=');
        \putenv('TEST_EMPTY=');

        static::assertEquals('', Env::get('foo'));
        static::assertEquals('', Env::get('TEST_EMPTY'));

        \putenv('foo=empty');
        \putenv('TEST_EMPTY=empty');

        static::assertEquals('', Env::get('foo'));
        static::assertEquals('', Env::get('TEST_EMPTY'));

        \putenv('foo=(empty)');
        \putenv('TEST_EMPTY=(empty)');

        static::assertEquals('', Env::get('foo'));
        static::assertEquals('', Env::get('TEST_EMPTY'));
    }

    public function testEnvNull(): void
    {
        \putenv('foo=null');
        \putenv('TEST_NULL=null');

        static::assertEquals('', Env::get('foo'));
        static::assertEquals('', Env::get('TEST_NULL'));

        \putenv('foo=(null)');
        \putenv('TEST_NULL=(null)');

        static::assertEquals('', Env::get('foo'));
        static::assertEquals('', Env::get('TEST_NULL'));
    }

    public function testEnvWithNumber(): void
    {
        \putenv('foo=25');
        \putenv('TEST_NUM=25');

        static::assertEquals('25', Env::get('foo'));
        static::assertSame(25, Env::get('TEST_NUM'));
    }

    public function testEnvWithBase64(): void
    {
        \putenv('foo=base64:dGVzdA==');
        \putenv('TEST_BASE64=base64:dGVzdA==');

        static::assertEquals('test', Env::get('foo'));
        static::assertSame('test', Env::get('TEST_BASE64'));
    }

    public function testEnvWithNotSetValue(): void
    {
        static::assertFalse(Env::get('NOT_SET', false));
        static::assertSame('test', Env::get('NOT_SET', function () {
            return 'test';
        }));
    }
}
