<?php
namespace Viserio\Exception\Tests;

use Psr\Log\LoggerInterface;
use Narrowspark\TestingHelper\Traits\MockeryTrait;
use Viserio\Contracts\{
    Config\Manager as ConfigManagerContract,
    Exception\Displayer as DisplayerContract,
    Exception\Transformer as TransformerContract,
    Exception\Exception\FatalThrowableError,
    Exception\Exception\FlattenException
};
use Viserio\Exception\Displayers\{
    HtmlDisplayer,
    JsonDisplayer
};
use Viserio\Exception\{
    Handler,
    ExceptionInfo
};

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

        $this->assertSame(2, count($handler->getDisplayers()));
    }
}
