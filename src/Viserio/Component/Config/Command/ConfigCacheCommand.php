<?php
declare(strict_types=1);
namespace Viserio\Component\Config\Command;

use Narrowspark\PrettyArray\PrettyArray;
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

        $returnValue = \file_put_contents(
            $this->getCachedConfigPath(),
            '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return ' . PrettyArray::print($this->getConfiguration()) . ';' . \PHP_EOL
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
