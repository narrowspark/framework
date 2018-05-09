<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Command;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;

class ConfigCacheCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'config:cache';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'config:cache
        [dir= : The config cache dir.]
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
        $this->call('config:clear');

        $returnValue = \file_put_contents(
            $this->getCachedConfigPath(),
            '<?php return ' . \var_export($this->getConfiguration(), true) . ';' . PHP_EOL
        );

        $this->info('Configuration cached successfully!');

        return (int) $returnValue;
    }

    /**
     * Get the cached config file path.
     *
     * @return string
     */
    protected function getCachedConfigPath(): string
    {
        return $this->argument('dir') . DIRECTORY_SEPARATOR . 'config.cache.php';
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
