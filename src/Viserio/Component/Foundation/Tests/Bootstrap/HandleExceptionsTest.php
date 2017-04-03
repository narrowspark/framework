<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Exception\Providers\ExceptionServiceProvider;
use Viserio\Component\Foundation\Bootstrap\HandleExceptions;

class HandleExceptionsTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $bootstraper = new HandleExceptions();
        $handler     = $this->mock(HandlerContract::class);
        $handler->shouldReceive('register')
            ->once();

        $app = $this->mock(ApplicationContract::class);
        $app->shouldReceive('register')
            ->once()
            ->with(ExceptionServiceProvider::class);
        $app->shouldReceive('get')
            ->once()
            ->with(HandlerContract::class)
            ->andReturn($handler);

        $bootstraper->bootstrap($app);
    }
}
