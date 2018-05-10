<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Command;

use Viserio\Component\Console\Command\Command;

class ConfigClearCommand extends Command
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
        return $this->argument('dir') . DIRECTORY_SEPARATOR . 'config.cache.php';
    }
}
