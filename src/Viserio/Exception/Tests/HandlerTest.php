<?php
namespace Viserio\Exception\Tests;

use Exception;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Psr\Log\LoggerInterface;
use Viserio\Contracts\{
    Config\Manager as ConfigManagerContract,
    Exception\Displayer as DisplayerContract,
    Exception\Transformer as TransformerContract,
    Exception\Exception\FatalThrowableError,
    Exception\Exception\FlattenException
};
use Viserio\Exception\{
    Handler,
    ExceptionInfo,
    ExceptionIdentifier
};
use Viserio\Exception\Displayers\{
    HtmlDisplayer,
    JsonDisplayer
};
use Viserio\Exception\Filters\VerboseFilter;
use Viserio\Exception\Transformers\CommandLineTransformer;

class HandlerTest extends \PHPUnit_Framework_TestCase
{
    use MockeryTrait;

    public function testAddAndGetDisplayer()
    {
        $handler = new Handler(
            $this->mock(ConfigManagerContract::class),
            $this->mock(LoggerInterface::class)
        );

        $info = $this->mock(ExceptionInfo::class);

        $handler->addDisplayer(new HtmlDisplayer($info, ''));
        $handler->addDisplayer(new JsonDisplayer($info));
        $handler->addDisplayer(new JsonDisplayer($info));

        $this->assertSame(2, count($handler->getDisplayers()));
    }

    public function testAddAndGetTransformer()
    {
        $handler = new Handler(
            $this->mock(ConfigManagerContract::class),
            $this->mock(LoggerInterface::class)
        );

        $handler->addTransformer(new CommandLineTransformer());
        $handler->addTransformer(new CommandLineTransformer());

        $this->assertSame(1, count($handler->getTransformers()));
    }

    public function testAddAndGetFilter()
    {
        $handler = new Handler(
            $this->mock(ConfigManagerContract::class),
            $this->mock(LoggerInterface::class)
        );

        $handler->addFilter($this->mock(VerboseFilter::class));
        $handler->addFilter($this->mock(VerboseFilter::class));

        $this->assertSame(1, count($handler->getFilters()));
    }

    public function testReportError($value='')
    {
        $exception = new Exception();
        $id = (new ExceptionIdentifier())->identify($exception);

        $log = $this->mock(LoggerInterface::class);
        $log
            ->shouldReceive('error')
            ->once($exception, ['identification' => ['id' => $id]]);

        $config = $this->mock(ConfigManagerContract::class);
        $config
            ->shouldReceive('get')
            ->twice()
            ->andReturn([]);

        $handler = new Handler(
            $config,
            $log
        );

        $handler->report($exception);
    }

    public function testReportCritical($value='')
    {
        $exception = new FatalThrowableError(new Exception());
        $id = (new ExceptionIdentifier())->identify($exception);

        $log = $this->mock(LoggerInterface::class);
        $log
            ->shouldReceive('critical')
            ->once($exception, ['identification' => ['id' => $id]]);

        $config = $this->mock(ConfigManagerContract::class);
        $config
            ->shouldReceive('get')
            ->twice()
            ->andReturn([]);

        $handler = new Handler(
            $config,
            $log
        );

        $handler->report($exception);
    }
}
