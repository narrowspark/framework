<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Command;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;

class UpCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'app:up';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Bring the application out of maintenance mode';

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $kernel = $this->getContainer()->get(ConsoleKernelContract::class);

        @\unlink($kernel->getStoragePath('framework/down'));

        $this->info('Application is now live.');

        return 0;
    }
}
