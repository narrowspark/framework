<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Command;

use Viserio\Component\Config\Command\ConfigCacheCommand as BaseConfigCacheCommand;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;

class ConfigCacheCommand extends BaseConfigCacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'config:cache';

    /**
     * {@inheritdoc}
     */
    protected $name = 'config:cache';

    /**
     * {@inheritdoc}
     */
    protected $signature;

    /**
     * {@inheritdoc}
     */
    protected function getCachedConfigPath(): string
    {
        return $this->getContainer()->get(ConsoleKernelContract::class)->getStoragePath('framework/config.cache.php');
    }
}
