<?php
declare(strict_types=1);
namespace Viserio\Component\Pipeline\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Pipeline\Tests\Fixture\PipelineInvokePipe;
use Viserio\Component\Pipeline\Tests\Fixture\PipelineTestParameterPipe;
use Viserio\Component\Pipeline\Tests\Fixture\PipelineTestPipeOne;

/**
 * @internal
 */
final class PipelineTest extends TestCase
{
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->container = new ArrayContainer([
            'PipelineTestPipeOne'       => new PipelineTestPipeOne(),
            'PipelineTestParameterPipe' => new PipelineTestParameterPipe(),
        ]);
    }

    public function testPipelineBasicUsage(): void
    {
        $pipeTwo = function ($piped, $next) {
            $_SERVER['__test.pipe.two'] = $piped;

            return $next($piped);
        };

        $result = (new Pipeline())
            ->setContainer($this->container)
            ->send('foo')
            ->through(['PipelineTestPipeOne', $pipeTwo])
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('foo', $result);
        $this->assertEquals('foo', $_SERVER['__test.pipe.one']);
        $this->assertEquals('foo', $_SERVER['__test.pipe.two']);

        unset($_SERVER['__test.pipe.one'], $_SERVER['__test.pipe.two']);
    }

    public function testPipelineUsageWithParameters(): void
    {
        $parameters = ['one', 'two'];

        $result = (new Pipeline())
            ->setContainer($this->container)
            ->send('foo')
            ->through('PipelineTestParameterPipe:' . \implode(',', $parameters))
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('foo', $result);
        $this->assertEquals($parameters, $_SERVER['__test.pipe.parameters']);

        unset($_SERVER['__test.pipe.parameters']);
    }

    public function testPipelineViaChangesTheMethodBeingCalledOnThePipes(): void
    {
        $result = (new Pipeline())
            ->setContainer($this->container)
            ->send('data')
            ->through('PipelineTestPipeOne')
            ->via('differentMethod')
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('data', $result);
    }

    public function testPipelineViaContainerToThrowException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Class [Controller] is not being managed by the container.');

        (new Pipeline())
            ->setContainer($this->container)
            ->send('data')
            ->through('Controller')
            ->via('differentMethod')
            ->then(function ($piped) {
                return $piped;
            });
    }

    public function testPipelineViaObject(): void
    {
        $result = (new Pipeline())
            ->send('foo')
            ->through([new PipelineTestPipeOne()])
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('foo', $result);
        $this->assertEquals('foo', $_SERVER['__test.pipe.one']);

        unset($_SERVER['__test.pipe.one']);
    }

    public function testPipelineInvoke(): void
    {
        $parameters = ['one', 'two'];

        $result = (new Pipeline())
            ->send('foo')
            ->through([[PipelineInvokePipe::class, $parameters]])
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('foo', $result);
        $this->assertEquals($parameters, $_SERVER['__test.pipe.parameters']);

        unset($_SERVER['__test.pipe.one']);
    }
}
