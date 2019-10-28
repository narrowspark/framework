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

namespace Viserio\Component\Config\Tests\ParameterProcessor;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor;
use Viserio\Component\Config\Repository;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\ParameterProcessor\AbstractParameterProcessor
 * @covers \Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor
 */
final class EnvParameterProcessorTest extends TestCase
{
    /** @var \Viserio\Component\Config\Repository */
    private $repository;

    /** @var \Viserio\Component\Config\ParameterProcessor\EnvParameterProcessor */
    private $processor;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new Repository();
        $this->processor = new EnvParameterProcessor();

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
        self::assertTrue($this->processor->supports('%' . EnvParameterProcessor::getReferenceKeyword() . ':test%'));
        self::assertFalse($this->processor->supports('test'));
    }

    public function testGetReferenceKeyword(): void
    {
        self::assertSame('env', EnvParameterProcessor::getReferenceKeyword());
    }

    public function testProcess(): void
    {
        \putenv('LOCAL=local');
        \putenv('foo=bar');
        \putenv('TEST_NORMAL=teststring');
        \putenv('TEST_FOO="teststring"');

        self::assertSame('local', $this->processor->process('%env:LOCAL%'));
        self::assertEquals('bar', $this->processor->process('%env:foo%'));
        self::assertSame('teststring', $this->processor->process('%env:TEST_NORMAL%'));
        self::assertEquals('bar/test', $this->processor->process('%env:foo%/test'));
        self::assertEquals('teststring/test', $this->processor->process('%env:TEST_FOO%/test'));

        $this->repository->set('foo', '%env:LOCAL%');

        self::assertSame('local', $this->repository->get('foo'));
    }

    public function testEnvWithQuotes(): void
    {
        \putenv('foo="bar"');
        \putenv('TEST_QUOTES="teststring"');

        self::assertEquals('bar', $this->processor->process('%env:foo%'));
        self::assertSame('teststring', $this->processor->process('%env:TEST_QUOTES%'));
    }

    public function testEnvTrue(): void
    {
        \putenv('foo=true');
        \putenv('TEST_TRUE=true');

        self::assertTrue($this->processor->process('%env:TEST_TRUE%'));

        \putenv('foo=(true)');
        \putenv('TEST_TRUE=(true)');

        self::assertTrue($this->processor->process('%env:TEST_TRUE%'));
    }

    public function testEnvFalse(): void
    {
        \putenv('foo=false');
        \putenv('TEST_FALSE=false');

        self::assertFalse($this->processor->process('%env:TEST_FALSE%'));

        \putenv('foo=(false)');
        \putenv('TEST_FALSE=(false)');

        self::assertFalse($this->processor->process('%env:TEST_FALSE%'));
    }

    public function testEnvEmpty(): void
    {
        \putenv('foo=');
        \putenv('TEST_EMPTY=');

        self::assertEquals('', $this->processor->process('%env:foo%'));
        self::assertEquals('', $this->processor->process('%env:TEST_EMPTY%'));

        \putenv('foo=empty');
        \putenv('TEST_EMPTY=empty');

        self::assertEquals('', $this->processor->process('%env:foo%'));
        self::assertEquals('', $this->processor->process('%env:TEST_EMPTY%'));

        \putenv('foo=(empty)');
        \putenv('TEST_EMPTY=(empty)');

        self::assertEquals('', $this->processor->process('%env:foo%'));
        self::assertEquals('', $this->processor->process('%env:TEST_EMPTY%'));
    }

    public function testEnvNull(): void
    {
        \putenv('foo=null');
        \putenv('TEST_NULL=null');

        self::assertEquals('', $this->processor->process('%env:foo%'));
        self::assertEquals('', $this->processor->process('%env:TEST_NULL%'));

        \putenv('foo=(null)');
        \putenv('TEST_NULL=(null)');

        self::assertEquals('', $this->processor->process('%env:foo%'));
        self::assertEquals('', $this->processor->process('%env:TEST_NULL%'));
    }

    public function testEnvWithNumber(): void
    {
        \putenv('foo=25');
        \putenv('TEST_NUM=25');

        self::assertEquals('25', $this->processor->process('%env:foo%'));
        self::assertSame(25, $this->processor->process('%env:TEST_NUM%'));
    }

    public function testEnvWithBase64(): void
    {
        \putenv('foo=base64:dGVzdA==');
        \putenv('TEST_BASE64=base64:dGVzdA==');

        self::assertEquals('test', $this->processor->process('%env:foo%'));
        self::assertSame('test', $this->processor->process('%env:TEST_BASE64%'));
    }

    public function testWithoutSetEnv(): void
    {
        self::assertSame('NOT_SET', $this->processor->process('%env:NOT_SET%'));
    }
}
