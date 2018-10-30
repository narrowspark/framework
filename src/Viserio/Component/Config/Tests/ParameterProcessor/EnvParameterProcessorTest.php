<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Tests\ParameterProcessor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor;
use Viserio\Component\Config\Repository;

/**
 * @internal
 */
final class EnvParameterProcessorTest extends TestCase
{
    /**
     * @var \Viserio\Component\Config\Repository
     */
    private $repository;

    /**
     * @var \Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor
     */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Repository();
        $this->processor  = new EnvParameterProcessor();

        $this->repository->addParameterProcessor($this->processor);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        \putenv('LOCAL=');
        \putenv('TEST_TRUE=');
        \putenv('TEST_FALSE=');
        \putenv('TEST_NULL=');
        \putenv('TEST_NUM=');
        \putenv('TEST_EMPTY=');
        \putenv('TEST_NORMAL=');
        \putenv('TEST_FOO=');
        \putenv('TEST_QUOTES=');
        \putenv('TEST_BASE64=');
        \putenv('foo=');
        \putenv('LOCAL');
        \putenv('TEST_TRUE');
        \putenv('TEST_FALSE');
        \putenv('TEST_NULL');
        \putenv('TEST_NUM');
        \putenv('TEST_EMPTY');
        \putenv('TEST_NORMAL');
        \putenv('TEST_FOO');
        \putenv('TEST_QUOTES');
        \putenv('TEST_BASE64');
        \putenv('foo');
    }

    public function testSupports(): void
    {
        $this->assertTrue($this->processor->supports('%' . EnvParameterProcessor::getReferenceKeyword() . ':test%'));
        $this->assertFalse($this->processor->supports('test'));
    }

    public function testGetReferenceKeyword(): void
    {
        $this->assertSame('env', EnvParameterProcessor::getReferenceKeyword());
    }

    public function testProcess(): void
    {
        \putenv('LOCAL=local');
        \putenv('foo=bar');
        \putenv('TEST_NORMAL=teststring');
        \putenv('TEST_FOO="teststring"');

        $this->assertSame('local', $this->processor->process('%env:LOCAL%'));
        $this->assertEquals('bar', $this->processor->process('%env:foo%'));
        $this->assertSame('teststring', $this->processor->process('%env:TEST_NORMAL%'));
        $this->assertEquals('bar/test', $this->processor->process('%env:foo%/test'));
        $this->assertEquals('teststring/test', $this->processor->process('%env:TEST_FOO%/test'));

        $this->repository->set('foo', '%env:LOCAL%');

        $this->assertSame('local', $this->repository->get('foo'));
    }

    public function testEnvWithQuotes(): void
    {
        \putenv('foo="bar"');
        \putenv('TEST_QUOTES="teststring"');

        $this->assertEquals('bar', $this->processor->process('%env:foo%'));
        $this->assertSame('teststring', $this->processor->process('%env:TEST_QUOTES%'));
    }

    public function testEnvTrue(): void
    {
        \putenv('foo=true');
        \putenv('TEST_TRUE=true');

        $this->assertTrue($this->processor->process('%env:TEST_TRUE%'));

        \putenv('foo=(true)');
        \putenv('TEST_TRUE=(true)');

        $this->assertTrue($this->processor->process('%env:TEST_TRUE%'));
    }

    public function testEnvFalse(): void
    {
        \putenv('foo=false');
        \putenv('TEST_FALSE=false');

        $this->assertFalse($this->processor->process('%env:TEST_FALSE%'));

        \putenv('foo=(false)');
        \putenv('TEST_FALSE=(false)');

        $this->assertFalse($this->processor->process('%env:TEST_FALSE%'));
    }

    public function testEnvEmpty(): void
    {
        \putenv('foo=');
        \putenv('TEST_EMPTY=');

        $this->assertEquals('', $this->processor->process('%env:foo%'));
        $this->assertEquals('', $this->processor->process('%env:TEST_EMPTY%'));

        \putenv('foo=empty');
        \putenv('TEST_EMPTY=empty');

        $this->assertEquals('', $this->processor->process('%env:foo%'));
        $this->assertEquals('', $this->processor->process('%env:TEST_EMPTY%'));

        \putenv('foo=(empty)');
        \putenv('TEST_EMPTY=(empty)');

        $this->assertEquals('', $this->processor->process('%env:foo%'));
        $this->assertEquals('', $this->processor->process('%env:TEST_EMPTY%'));
    }

    public function testEnvNull(): void
    {
        \putenv('foo=null');
        \putenv('TEST_NULL=null');

        $this->assertEquals('', $this->processor->process('%env:foo%'));
        $this->assertEquals('', $this->processor->process('%env:TEST_NULL%'));

        \putenv('foo=(null)');
        \putenv('TEST_NULL=(null)');

        $this->assertEquals('', $this->processor->process('%env:foo%'));
        $this->assertEquals('', $this->processor->process('%env:TEST_NULL%'));
    }

    public function testEnvWithNumber(): void
    {
        \putenv('foo=25');
        \putenv('TEST_NUM=25');

        $this->assertEquals('25', $this->processor->process('%env:foo%'));
        $this->assertSame(25, $this->processor->process('%env:TEST_NUM%'));
    }

    public function testEnvWithBase64(): void
    {
        \putenv('foo=base64:dGVzdA==');
        \putenv('TEST_BASE64=base64:dGVzdA==');

        $this->assertEquals('test', $this->processor->process('%env:foo%'));
        $this->assertSame('test', $this->processor->process('%env:TEST_BASE64%'));
    }

    public function testWithoutSetEnv(): void
    {
        $this->assertSame('NOT_SET', $this->processor->process('%env:NOT_SET%'));
    }
}
