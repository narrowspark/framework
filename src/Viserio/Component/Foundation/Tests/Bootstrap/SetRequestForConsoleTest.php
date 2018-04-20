<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Interop\Http\Factory\ServerRequestFactoryInterface;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Foundation\Bootstrap\SetRequestForConsole;
use Viserio\Component\Foundation\Console\Kernel;

class SetRequestForConsoleTest extends MockeryTestCase
{
    public function testBootstrap(): void
    {
        $kernel = new class() extends Kernel {
            /**
             * The bootstrap classes for the application.
             *
             * @var array
             */
            protected $bootstrappers = [
                SetRequestForConsole::class,
            ];

            protected function registerBaseServiceProviders(): void
            {
            }
        };
        $kernel->setKernelConfigurations([
            'viserio' => [
                'app' => [
                    'env'   => 'prod',
                    'debug' => true,
                    'url'   => 'http://localhost',
                ],
            ],
        ]);

        $container     = $kernel->getContainer();
        $serverRequest = $this->mock(ServerRequestInterface::class);

        $request = $this->mock(ServerRequestFactoryInterface::class);
        $request->shouldReceive('createServerRequest')
            ->once()
            ->with('GET', 'http://localhost')
            ->andReturn($serverRequest);
        $container->instance(ServerRequestFactoryInterface::class, $request);

        $kernel->bootstrap();

        self::assertInstanceOf(ServerRequestInterface::class, $container->get(ServerRequestInterface::class));
    }
}
