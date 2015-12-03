<?php
namespace Viserio\Pipeline\Tests;

use Mockery as Mock;
use Viserio\Pipeline\Pipeline;

class PipelineTest extends \PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mock::close();
    }

    public function testPipelineBasicUsage()
    {
        $pipeTwo = function ($piped, $next) {
            $_SERVER['__test.pipe.two'] = $piped;
            return $next($piped);
        };

        $result = (new Pipeline($this->getMockContainer(new PipelineTestPipeOne)))
            ->send('foo')
            ->through(['PipelineTestPipeOne', $pipeTwo])
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('foo', $result);
        $this->assertEquals('foo', $_SERVER['__test.pipe.one']);
        $this->assertEquals('foo', $_SERVER['__test.pipe.two']);

        unset($_SERVER['__test.pipe.one']);
        unset($_SERVER['__test.pipe.two']);
    }

    public function testPipelineUsageWithParameters()
    {
        $parameters = ['one', 'two'];

        $result = (new Pipeline($this->getMockContainer(new PipelineTestParameterPipe)))
            ->send('foo')
            ->through('PipelineTestParameterPipe:'.implode(',', $parameters))
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('foo', $result);
        $this->assertEquals($parameters, $_SERVER['__test.pipe.parameters']);

        unset($_SERVER['__test.pipe.parameters']);
    }

    public function testPipelineViaChangesTheMethodBeingCalledOnThePipes()
    {
        $result = (new Pipeline($this->getMockContainer(new PipelineTestPipeOne)))->send('data')
            ->through('PipelineTestPipeOne')
            ->via('differentMethod')
            ->then(function ($piped) {
                return $piped;
            });

        $this->assertEquals('data', $result);
    }

    public function getMockContainer($class)
    {
        $mock = Mock::mock('\Interop\Container\ContainerInterface');
        $mock->shouldReceive('get')
            ->andReturn($class)
            ->mock();

        return $mock;
    }
}


class PipelineTestPipeOne
{
    public function handle($piped, $next)
    {
        $_SERVER['__test.pipe.one'] = $piped;
        return $next($piped);
    }

    public function differentMethod($piped, $next)
    {
        return $next($piped);
    }
}

class PipelineTestParameterPipe
{
    public function handle($piped, $next, $parameter1 = null, $parameter2 = null)
    {
        $_SERVER['__test.pipe.parameters'] = [$parameter1, $parameter2];
        return $next($piped);
    }
}
