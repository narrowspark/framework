<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Providers\ConfigureLoggingServiceProvider;
use Viserio\Component\Log\Providers\LoggerServiceProvider;

class HandleLogger extends AbstractLoadFiles implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();

        $container->register(new LoggerServiceProvider());
        $container->register(new ConfigureLoggingServiceProvider());
    }
}
