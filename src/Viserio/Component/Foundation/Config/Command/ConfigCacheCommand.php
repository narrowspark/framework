<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Foundation\Config\Command;

use Viserio\Component\Config\Command\ConfigCacheCommand as BaseConfigCacheCommand;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;

class ConfigCacheCommand extends BaseConfigCacheCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'config:cache';

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
        return $this->getContainer()->get(ConsoleKernelContract::class)->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'config.cache.php');
    }
}
