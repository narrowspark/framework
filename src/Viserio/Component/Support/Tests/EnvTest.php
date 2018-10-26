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

        $this->assertEquals('bar', Env::get('foo'));
        $this->assertSame('teststring', Env::get('TEST_NORMAL'));
    }

    public function testEnvWithQuotes(): void
    {
        \putenv('foo="bar"');
        \putenv('TEST_QUOTES="teststring"');

        $this->assertEquals('bar', Env::get('foo'));
        $this->assertSame('teststring', Env::get('TEST_QUOTES'));
    }

    public function testEnvTrue(): void
    {
        \putenv('foo=true');
        \putenv('TEST_TRUE=true');

        $this->assertTrue(env('foo'));
        $this->assertTrue(Env::get('TEST_TRUE'));

        \putenv('foo=(true)');
        \putenv('TEST_TRUE=(true)');

        $this->assertTrue(env('foo'));
        $this->assertTrue(Env::get('TEST_TRUE'));
    }

    public function testEnvFalse(): void
    {
        \putenv('foo=false');
        \putenv('TEST_FALSE=false');

        $this->assertFalse(env('foo'));
        $this->assertFalse(Env::get('TEST_FALSE'));

        \putenv('foo=(false)');
        \putenv('TEST_FALSE=(false)');

        $this->assertFalse(env('foo'));
        $this->assertFalse(Env::get('TEST_FALSE'));
    }

    public function testEnvEmpty(): void
    {
        \putenv('foo=');
        \putenv('TEST_EMPTY=');

        $this->assertEquals('', Env::get('foo'));
        $this->assertEquals('', Env::get('TEST_EMPTY'));

        \putenv('foo=empty');
        \putenv('TEST_EMPTY=empty');

        $this->assertEquals('', Env::get('foo'));
        $this->assertEquals('', Env::get('TEST_EMPTY'));

        \putenv('foo=(empty)');
        \putenv('TEST_EMPTY=(empty)');

        $this->assertEquals('', Env::get('foo'));
        $this->assertEquals('', Env::get('TEST_EMPTY'));
    }

    public function testEnvNull(): void
    {
        \putenv('foo=null');
        \putenv('TEST_NULL=null');

        $this->assertEquals('', Env::get('foo'));
        $this->assertEquals('', Env::get('TEST_NULL'));

        \putenv('foo=(null)');
        \putenv('TEST_NULL=(null)');

        $this->assertEquals('', Env::get('foo'));
        $this->assertEquals('', Env::get('TEST_NULL'));
    }

    public function testEnvWithNumber(): void
    {
        \putenv('foo=25');
        \putenv('TEST_NUM=25');

        $this->assertEquals('25', Env::get('foo'));
        $this->assertSame(25, Env::get('TEST_NUM'));
    }

    public function testEnvWithBase64(): void
    {
        \putenv('foo=base64:dGVzdA==');
        \putenv('TEST_BASE64=base64:dGVzdA==');

        $this->assertEquals('test', Env::get('foo'));
        $this->assertSame('test', Env::get('TEST_BASE64'));
    }

    public function testEnvWithNotSetValue(): void
    {
        $this->assertFalse(Env::get('NOT_SET', false));
        $this->assertSame('test', Env::get('NOT_SET', function () {
            return 'test';
        }));
    }
}
