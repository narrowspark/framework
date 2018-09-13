<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Command;

use ReflectionClass;
use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Parser\Dumper;

class OptionDumpCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected static $defaultName = 'option:dump';

    /**
     * {@inheritdoc}
     */
    protected $signature = 'option:dump 
        [class : Name of the class to reflect.]
        [dir : Path to the config dir.]
        [--format=php : The output format (php, json, xml, json).]
        [--overwrite : Overwrite existent class config.]
        [--merge : Merge existent class config with a new class config.]
        [--show : You will see the config and be asked before the config is written to a file.]
    ';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Dumps config files for found classes with RequiresConfig interface.';

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException if dir cant be created or is not writable
     */
    public function handle(): int
    {
        $format = $this->option('format');
        $dumper = null;

        if ($this->container !== null && $this->getContainer()->has(Dumper::class)) {
            $dumper = $this->getContainer()->get(Dumper::class);
        }

        if ($dumper === null && $format !== 'php') {
            $this->error('Only the php format is supported; use composer req viserio/parser to get [json], [xml], [yml] output.');

            return 1;
        }

        $dirPath = $this->argument('dir');

        if (! \is_dir($dirPath) && ! @\mkdir($dirPath, 0777, true)) {
            throw new \RuntimeException(\sprintf('Config directory [%s] cannot be created or is write protected.', $dirPath));
        }

        $className = $this->argument('class');
        $configs   = $this->getConfigReader()->readConfig(new ReflectionClass($className));

        foreach ($configs as $key => $config) {
            $file = $dirPath . \DIRECTORY_SEPARATOR . $key . '.' . $format;

            if ($this->hasOption('merge') && \file_exists($file)) {
                $existingConfig = includeFile($file);
                $config         = \array_replace_recursive($existingConfig, $config);
            }

            if ($dumper !== null && $format !== 'php') {
                $content = $dumper->dump($config, $format) . \PHP_EOL;
            } else {
                $content = '<?php' . \PHP_EOL . 'declare(strict_types=1);' . \PHP_EOL . \PHP_EOL . 'return ';
                $content .= VarExporter::export($config) . ';' . \PHP_EOL;
            }

            if ($this->hasOption('show')) {
                $this->info('Output array:' . \PHP_EOL . \PHP_EOL . $content);

                if ($this->confirm(\sprintf('Write content to [%s]?', $file)) === false) {
                    continue;
                }
            }

            $this->info(\sprintf('Dumping [%s] configuration to [%s].', $className, $file));
            $this->putContentToFile($file, $content, $key);
        }

        return 0;
    }

    /**
     * Put the created content to file.
     *
     * @param string $file
     * @param string $content
     * @param string $key
     *
     * @return void
     */
    private function putContentToFile(string $file, string $content, string $key): void
    {
        if ($this->hasOption('overwrite') || ! \file_exists($file)) {
            \file_put_contents($file, $content);
        } else {
            if ($this->hasOption('merge')) {
                $confirmed = true;
            } else {
                $confirmed = $this->confirm(\sprintf('Do you really wish to overwrite %s', $key));
            }

            if ($confirmed) {
                \file_put_contents($file, $content);
            }
        }
    }

    /**
     * Get a modified OptionsReader instance.
     *
     * @return \Viserio\Component\OptionsResolver\Command\OptionsReader
     */
    private function getConfigReader(): OptionsReader
    {
        $command = $this;

        return new class($command) extends OptionsReader {
            /**
             * A OptionDumpCommand instance.
             *
             * @var \Viserio\Component\OptionsResolver\Command\OptionDumpCommand
             */
            private $command;

            /**
             * @param \Viserio\Component\OptionsResolver\Command\OptionDumpCommand $command
             */
            public function __construct(OptionDumpCommand $command)
            {
                $this->command = $command;
            }

            /**
             * Read the mandatory options and ask for the value.
             *
             * @param string $className
             * @param array  $dimensions
             * @param array  $mandatoryOptions
             *
             * @return array
             */
            protected function readMandatoryOption(string $className, array $dimensions, array $mandatoryOptions): array
            {
                $options = [];

                foreach ($mandatoryOptions as $key => $mandatoryOption) {
                    if (! \is_scalar($mandatoryOption)) {
                        $options[$key] = $this->readMandatoryOption($className, $dimensions, $mandatoryOptions[$key]);

                        continue;
                    }

                    $options[$mandatoryOption] = $this->command->ask(
                        \sprintf(
                            '%s: Please enter the following mandatory value for [%s]',
                            $className,
                            \implode('.', $dimensions) . '.' . $mandatoryOption
                        )
                    );
                }

                return $options;
            }
        };
    }
}

/**
 * Scope isolated include.
 *
 * Prevents access to $this/self from included files.
 *
 * @param string $file
 *
 * @return array
 */
function includeFile(string $file): array
{
    return (array) include $file;
}
