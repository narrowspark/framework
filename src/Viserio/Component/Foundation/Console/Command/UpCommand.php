<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Console\Command;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\Console\Kernel as ConsoleKernelContract;

class UpCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'up';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Bring the application out of maintenance mode';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $kernel = $this->getContainer()->get(ConsoleKernelContract::class);

        @unlink($kernel->storagePath('framework/down'));

        $this->info('Application is now live.');
    }
}
