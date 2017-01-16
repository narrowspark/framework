<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Commands;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\Config\Repository as RepositoryContract;

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
        $config = $this->container->get(RepositoryContract::class);

        @unlink($config->get('path.storage') . '/framework/down');

        $this->info('Application is now live.');
    }
}
