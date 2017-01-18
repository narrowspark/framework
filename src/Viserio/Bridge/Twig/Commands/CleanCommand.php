<?php
declare(strict_types=1);
namespace Viserio\Bridge\Twig\Commands;

use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\Filesystem\Filesystem as FilesystemContract;

class CleanCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'twig:clean';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Clean the Twig Cache';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $container = $this->getContainer();
        $twig      = $container->get('twig');
        $files     = $container->get(FilesystemContract::class);

        $cacheDir = $twig->getCache();

        $files->deleteDirectory($cacheDir);

        if ($files->exists($cacheDir)) {
            $this->error('Twig cache failed to be cleaned.');
        } else {
            $this->info('Twig cache cleaned.');
        }
    }
}
