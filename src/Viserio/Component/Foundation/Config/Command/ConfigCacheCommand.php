<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Config\Command;

use Viserio\Component\Config\Command\ConfigCacheCommand as BaseConfigCacheCommand;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;

class ConfigCacheCommand extends BaseConfigCacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected $signature;

    /**
     * {@inheritdoc}
     */
    protected function callConfigClearCommand(): void
    {
        $this->call('config:clear');
    }

    /**
     * {@inheritdoc}
     */
    protected function getCachedConfigPath(): string
    {
        return $this->getContainer()->get(ConsoleKernelContract::class)->getStoragePath('framework/config.cache.php');
    }
}
