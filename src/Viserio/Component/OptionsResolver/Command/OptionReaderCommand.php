<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Command;

use ReflectionClass;
use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

class OptionReaderCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'option:read';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'option:read 
        [class : Name of the class to reflect.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Reads the provided configuration file and displays options for the provided class name.';

    /**
     * {@inheritdoc}
     */
    public function handle(): int
    {
        $className       = $this->argument('class');
        $reflectionClass = new ReflectionClass($className);

        $configs = (new OptionsReader())->readConfig($reflectionClass);

        if (\count($configs) !== 0) {
            $interfaces = \array_flip($reflectionClass->getInterfaceNames());

            if (isset($interfaces[RequiresComponentConfigContract::class])) {
                $dimensions = $className::getDimensions();
                $configs    = $configs[\end($dimensions)];
            }
        }

        $this->info('Output array:' . \PHP_EOL . \PHP_EOL . VarExporter::export($configs));

        return 0;
    }
}
