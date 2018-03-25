<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Component\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;

class HttpHandleExceptions extends AbstractLoadFiles implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $kernel->getContainer()->get(HttpHandlerContract::class)->register();
    }
}
