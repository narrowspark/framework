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

use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Contract\Config\Repository as RepositoryContract;

class ConfigCacheCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'config:cache';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'config:cache
        [dir : The config cache dir.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a cache file for faster configuration loading';

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $this->callConfigClearCommand();

        $exportedConfig = VarExporter::export($this->getConfiguration());

        $returnValue = \file_put_contents(
            $this->getCachedConfigPath(),
            "<?php\ndeclare(strict_types=1);\n\nreturn {$exportedConfig}};\n"
        );

        $this->info('Configuration cached successfully!');

        return (int) $returnValue;
    }

    /**
     * Call the config clear command.
     *
     * @return void
     */
    protected function callConfigClearCommand(): void
    {
        $this->call('config:clear', ['dir' => $this->argument('dir')]);
    }

    /**
     * Get the cached config file path.
     *
     * @return string
     */
    protected function getCachedConfigPath(): string
    {
        return $this->argument('dir') . \DIRECTORY_SEPARATOR . 'config.cache.php';
    }

    /**
     * Get all configuration.
     *
     * @return array
     */
    protected function getConfiguration(): array
    {
        return $this->getContainer()->get(RepositoryContract::class)->getAllProcessed();
    }
}
