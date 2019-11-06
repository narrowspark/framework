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

use Viserio\Component\Config\Command\ConfigClearCommand as BaseConfigClearCommand;
use Viserio\Contract\Console\Kernel as ConsoleKernelContract;
use const DIRECTORY_SEPARATOR;

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
        return $this->getContainer()->get(ConsoleKernelContract::class)->getStoragePath('framework' . DIRECTORY_SEPARATOR . 'config.cache.php');
    }
}
