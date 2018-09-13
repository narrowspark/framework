<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Command;

use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Console\Command\AbstractCommand;

class OptionReaderCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'option:show';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'option:show 
        [class : Name of the class to reflect.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Reads the provided configuration file and displays options for the provided class name.';

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException if dir cant be created or is not writable
     */
    public function handle(): int
    {
        $configs = (new OptionsReader())->readConfig($this->argument('class'));

        $this->info('Output array:' . \PHP_EOL . \PHP_EOL . VarExporter::export($configs));

        return 0;
    }
}
