<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Bootstrap;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;
use Viserio\Component\Contracts\Foundation\Application as ApplicationContract;
use Viserio\Component\Foundation\Bootstrap\RegisterStaticalProxys;
use Viserio\Component\Foundation\Providers\ConfigureLoggingServiceProvider;

class RegisterStaticalProxysTest extends MockeryTestCase
{
    public function testBootstrap()
    {
        $provider = new ConfigureLoggingServiceProvider();

        $bootstraper = new RegisterStaticalProxys();

        $config = $this->mock(RepositoryContract::class);
        $config->shouldReceive('get')
            ->once()
            ->with('app.aliases', [])
            ->andReturn([ConfigureLoggingServiceProvider::class]);

        $app = $this->mock(ApplicationContract::class);
        $app->shouldReceive('get')
            ->once()
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $bootstraper->bootstrap($app);
    }
}
