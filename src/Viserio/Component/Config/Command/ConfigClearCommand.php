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

namespace Viserio\Component\Config\Command;

use Viserio\Component\Console\Command\AbstractCommand;

class ConfigClearCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'config:clear';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'config:clear
        [dir : The config cache dir.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Remove the configuration cache file';

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $returnValue = @\unlink($this->getCachedConfigDirPath());

        $this->info('Configuration cache cleared!');

        return (int) $returnValue;
    }

    /**
     * Get the config cache dir path.
     *
     * @return string
     */
    protected function getCachedConfigDirPath(): string
    {
        return $this->argument('dir') . \DIRECTORY_SEPARATOR . 'config.cache.php';
    }
}
