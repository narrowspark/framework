<?php
declare(strict_types=1);
namespace Viserio\Pipeline\Tests;

use Narrowspark\TestingHelper\ArrayContainer;
use Viserio\Pipeline\Pipeline;
use Viserio\Pipeline\Tests\Fixture\PipelineInvokePipe;
use Viserio\Pipeline\Tests\Fixture\PipelineTestParameterPipe;
use Viserio\Pipeline\Tests\Fixture\PipelineTestPipeOne;

class PipelineTest extends \PHPUnit_Framework_TestCase
{
    protected $container;

    public function setUp()
    {
        $this->container = new ArrayContainer([
            'PipelineTestPipeOne' => new PipelineTestPipeOne(),
            'PipelineTestParameterPipe' => new PipelineTestParameterPipe(),
        ]);
    }

    public function testPipelineBasicUsage()
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

    public function testPipelineUsageWithParameters()
    {
        $parameters = ['one', 'two'];

        $result = (new Pipeline())
            ->setContainer($this->container)
            ->send('foo')
            ->through('PipelineTestParameterPipe:' . implode(',', $parameters))
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('foo', $result);
        $this->assertEquals($parameters, $_SERVER['__test.pipe.parameters']);

        unset($_SERVER['__test.pipe.parameters']);
    }

    public function testPipelineViaChangesTheMethodBeingCalledOnThePipes()
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

    public function testPipelineViaObject()
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

    public function testPipelineInvoke()
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
