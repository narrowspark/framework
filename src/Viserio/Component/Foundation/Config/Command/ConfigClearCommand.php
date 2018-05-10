<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Config\Command;

use Viserio\Component\Config\Command\ConfigClearCommand as BaseConfigClearCommand;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;

class ConfigClearCommand extends BaseConfigClearCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'config:clear';

    /**
     * {@inheritdoc}
     */
    protected $signature;

    /**
     * {@inheritdoc}
     */
    protected function getCachedConfigDirPath(): string
    {
        return $this->getContainer()->get(ConsoleKernelContract::class)->getStoragePath('framework/config.cache.php');
    }
}
