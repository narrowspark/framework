<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Pipeline\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Pipeline\Tests\Fixture\PipelineInvokePipe;
use Viserio\Component\Pipeline\Tests\Fixture\PipelineTestParameterPipe;
use Viserio\Component\Pipeline\Tests\Fixture\PipelineTestPipeOne;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class PipelineTest extends TestCase
{
    /** @var \Psr\Container\ContainerInterface */
    protected $container;

    /** @var array */
    private static $globalServer = [];

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new ArrayContainer([
            'PipelineTestPipeOne' => new PipelineTestPipeOne(),
            'PipelineTestParameterPipe' => new PipelineTestParameterPipe(),
        ]);
        self::$globalServer = $_SERVER;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER = self::$globalServer;
    }

    public function testPipelineBasicUsage(): void
    {
        $pipeTwo = static function ($piped, $next) {
            $_SERVER['__test.pipe.two'] = $piped;

            return $next($piped);
        };

        $result = (new Pipeline())
            ->setContainer($this->container)
            ->send('foo')
            ->through(['PipelineTestPipeOne', $pipeTwo])
            ->then(static function ($piped) {
                return $piped;
            });

        self::assertEquals('foo', $result);
        self::assertEquals('foo', $_SERVER['__test.pipe.one']);
        self::assertEquals('foo', $_SERVER['__test.pipe.two']);

        unset($_SERVER['__test.pipe.one'], $_SERVER['__test.pipe.two']);
    }

    public function testPipelineUsageWithParameters(): void
    {
        $parameters = ['one', 'two'];

        $result = (new Pipeline())
            ->setContainer($this->container)
            ->send('foo')
            ->through('PipelineTestParameterPipe:' . \implode(',', $parameters))
            ->then(static function ($piped) {
                return $piped;
            });

        self::assertEquals('foo', $result);
        self::assertEquals($parameters, $_SERVER['__test.pipe.parameters']);

        unset($_SERVER['__test.pipe.parameters']);
    }

    public function testPipelineViaChangesTheMethodBeingCalledOnThePipes(): void
    {
        $result = (new Pipeline())
            ->setContainer($this->container)
            ->send('data')
            ->through('PipelineTestPipeOne')
            ->via('differentMethod')
            ->then(static function ($piped) {
                return $piped;
            });

        self::assertEquals('data', $result);
    }

    public function testPipelineViaContainerToThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Class [Controller] is not being managed by the container.');

        (new Pipeline())
            ->setContainer($this->container)
            ->send('data')
            ->through('Controller')
            ->via('differentMethod')
            ->then(static function ($piped) {
                return $piped;
            });
    }

    public function testPipelineViaObject(): void
    {
        $result = (new Pipeline())
            ->send('foo')
            ->through([new PipelineTestPipeOne()])
            ->then(static function ($piped) {
                return $piped;
            });

        self::assertEquals('foo', $result);
        self::assertEquals('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineInvoke(): void
    {
        $parameters = ['one', 'two'];

        $result = (new Pipeline())
            ->send('foo')
            ->through([[PipelineInvokePipe::class, $parameters]])
            ->then(static function ($piped) {
                return $piped;
            });

        self::assertEquals('foo', $result);
        self::assertEquals($parameters, $_SERVER['__test.pipe.parameters']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineUsageWithCallable(): void
    {
        $function = function ($piped, $next) {
            $_SERVER['__test.pipe.one'] = 'foo';

            return $next($piped);
        };

        $result = (new Pipeline())
            ->send('foo')
            ->through([$function])
            ->then(
                function ($piped) {
                    return $piped;
                }
            );

        self::assertEquals('foo', $result);
        self::assertEquals('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }
}
