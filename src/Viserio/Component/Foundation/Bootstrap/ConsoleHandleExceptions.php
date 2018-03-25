<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Bootstrap;

use Viserio\Component\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;
use Viserio\Component\Contract\Foundation\Bootstrap as BootstrapContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;

class ConsoleHandleExceptions extends AbstractLoadFiles implements BootstrapContract
{
    /**
     * {@inheritdoc}
     */
    public function bootstrap(KernelContract $kernel): void
    {
        $kernel->getContainer()->get(ConsoleHandlerContract::class)->register();
    }
}
