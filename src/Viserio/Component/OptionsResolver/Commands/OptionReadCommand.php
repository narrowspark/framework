<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Commands;

use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Viserio\Component\Console\Command\Command;

class OptionReadCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'option:read';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Reads all classes with RequiresConfig interface.';

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $finder = new Finder();
        $files  = $finder->files()->name('*.php')->in(__DIR__.'/../../../');

        foreach ($files as $file) {
            \var_dump($file);
            $reflectionClass = new ReflectionClass($className);
            $interfaces = $reflectionClass->getInterfaceNames();

            if (in_array(RequiresConfig::class, $interfaces, true)) {
                $dimensions = $factory->dimensions();
            }
        }
    }
}
