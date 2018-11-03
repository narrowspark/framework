<?php
declare(strict_types=1);
namespace Viserio\Component\HttpFoundation\Console\Command;

use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\Console\Kernel as ConsoleKernelContract;

class UpCommand extends AbstractCommand
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

        @\unlink($kernel->getStoragePath('framework' . \DIRECTORY_SEPARATOR . 'down'));

        $this->info('Application is now live.');

        return 0;
    }
}
