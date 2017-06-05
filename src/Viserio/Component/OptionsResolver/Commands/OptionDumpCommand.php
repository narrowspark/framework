<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Commands;

use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Viserio\Component\Console\Command\Command;

class OptionDumpCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'option:dump';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Dumps config files for found classes with RequiresConfig interface.';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $files = Finder::create()->files()
            ->ignoreVCS(true)
            ->ignoreDotFiles(true)
            ->ignoreUnreadableDirs()
            ->contains('Viserio\\Component\\Contracts\\OptionsResolver')
            ->name('*.php')
            ->in(__DIR__.'/../../../');
    }
}
