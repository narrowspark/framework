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
        $reflectionClass = new ReflectionClass($className);
        $interfaces = $reflectionClass->getInterfaceNames();

        if (! $reflectionClass->isInternal() && ! $reflectionClass->isAbstract()) {
            $factory          = $reflectionClass->newInstanceWithoutConstructor();
            $dimensions       = [];
            $mandatoryOptions = [];
            $defaultOptions   = [];

            if (in_array(RequiresComponentConfigContract::class, $interfaces, true)) {
                $dimensions = $factory->getDimensions();
            }

            if (in_array(ProvidesDefaultOptionsContract::class, $interfaces, true)) {
                $defaultOptions = $factory->getDefaultOptions();
            }

            if (in_array(RequiresMandatoryOptionsContract::class, $interfaces, true)) {
                $mandatoryOptions = $factory->getMandatoryOptions();
            }

            $config = [];
        }
    }
}
