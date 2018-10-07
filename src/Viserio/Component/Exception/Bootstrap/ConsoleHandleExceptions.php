<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Bootstrap;

use Viserio\Component\Contract\Exception\ConsoleHandler as ConsoleHandlerContract;
use Viserio\Component\Contract\Foundation\BootstrapState as BootstrapStateContract;
use Viserio\Component\Contract\Foundation\Kernel as KernelContract;
use Viserio\Component\Foundation\Bootstrap\ConfigureKernel;

class ConsoleHandleExceptions implements BootstrapStateContract
{
    /**
     * {@inheritdoc}
     */
    public static function getPriority(): int
    {
        return 32;
    }

    /**
     * {@inheritdoc}
     */
    public static function getType(): string
    {
        return BootstrapStateContract::TYPE_AFTER;
    }

    /**
     * {@inheritdoc}
     */
    public static function getBootstrapper(): string
    {
        return ConfigureKernel::class;
    }

    /**
     * {@inheritdoc}
     */
    public static function bootstrap(KernelContract $kernel): void
    {
        $kernel->getContainer()->get(ConsoleHandlerContract::class)->register();
    }
}
