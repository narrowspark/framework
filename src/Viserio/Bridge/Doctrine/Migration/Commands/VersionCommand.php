<?php
declare(strict_types=1);
namespace Viserio\Bridge\Doctrine\Migration\Commands;

class VersionCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'doctrine:migrations:version';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'doctrine:migrations:version
        [version: The version to add or delete.]
        [--connection= : For a specific connection.]
        [--add : Add the specified version.]
        [--delete : Delete the specified version.]
        [--all : Apply to all the versions.]
        [--range-from= : Apply from specified version.]
        [--range-to= : Apply to specified version.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Manually add and delete migration versions from the version table.';

    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
    }
}
