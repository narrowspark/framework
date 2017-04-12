<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

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
        $contaienr = $kernel->getContainer();

        $contaienr->register(new ExceptionServiceProvider());

        $contaienr->get(HandlerContract::class)->register();
    }
}
