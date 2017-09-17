<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contract\Exception\Handler as HandlerContract;
use Viserio\Component\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Exception\Provider\ExceptionServiceProvider;

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
