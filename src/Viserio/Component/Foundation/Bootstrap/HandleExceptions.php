<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use ErrorException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Throwable;
use Viserio\Component\Contracts\Debug\ExceptionHandler as ExceptionHandlerContract;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contracts\Foundation\Kernel as KernelContract;
use Viserio\Component\Exception\Providers\ExceptionServiceProvider;

class HandleExceptions extends AbstractLoadFiles implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $container = $kernel->getContainer();

        $container->register(new ExceptionServiceProvider());

        $container->get(HandlerContract::class)->register();
    }
}
