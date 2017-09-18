<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Command;

use InvalidArgumentException;
use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Parser\Dumper;
use Viserio\Component\Support\Traits\ArrayPrettyPrintTrait;

class OptionDumpCommand extends Command
{
    use ArrayPrettyPrintTrait;

    /**
     * {@inheritdoc}
     */
    protected $signature = 'option:dump 
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
     * @throws \InvalidArgumentException if dir cant be created or is not writable
     */
    public function handle()
    {
        $configs = $this->getOptionsFromDeclaredClasses();
        $format  = $this->option('format');
        $dirPath = $this->argument('dir');
        $dumper  = null;

        if ($this->container !== null && $this->getContainer()->has(Dumper::class)) {
            $dumper = $this->getContainer()->get(Dumper::class);
        }

        if ($dumper === null && $format !== 'php') {
            $this->error('Only the php format is supported; use composer req viserio/parsers to get json, xml, yml output.');

            return 1;
        }

        self::generateDirectory($dirPath);

        foreach ($configs as $key => $config) {
            $file = $dirPath . '\\' . $key . '.' . $format;

            if ($this->hasOption('merge') && \file_exists($file)) {
                $existingConfig = (array) include $file;
                $config         = \array_replace_recursive($existingConfig, $config);
            }

            if ($dumper !== null) {
                $content = $dumper->dump($config, $format);
            } else {
                $content = '<?php
declare(strict_types=1);

return ' . $this->getPrettyPrintArray($config) . ';';
            }

            if ($this->hasOption('show')) {
                $this->info("Merged array:\n\n" . $content);

                if ($this->confirm(\sprintf('Write content to "%s"?', $file)) === false) {
                    continue;
                }
            }

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
                } else {
                    continue;
                }
            }
        }

        return 0;
    }

    /**
     * Return a array full of declared class options.
     *
     * @throws \ReflectionException
     *
     * @return array
     */
    private function getOptionsFromDeclaredClasses(): array
    {
        $configs = [];

        foreach (get_declared_classes() as $className) {
            $reflectionClass = new ReflectionClass($className);
            $interfaces      = \array_flip($reflectionClass->getInterfaceNames());

            if (isset($interfaces[RequiresConfigContract::class]) && ! $reflectionClass->isInternal() && ! $reflectionClass->isAbstract()) {
                $factory          = $reflectionClass->newInstanceWithoutConstructor();
                $dimensions       = [];
                $mandatoryOptions = [];
                $defaultOptions   = [];
                $key              = null;

                if (isset($interfaces[RequiresComponentConfigContract::class])) {
                    $dimensions = (array) $factory->getDimensions();
                    $key        = \end($dimensions);
                }

                if (isset($interfaces[ProvidesDefaultOptionsContract::class])) {
                    $defaultOptions = (array) $factory->getDefaultOptions();
                }

                if (isset($interfaces[RequiresMandatoryOptionsContract::class])) {
                    $mandatoryOptions = $this->readMandatoryOption($factory->getMandatoryOptions());
                }

                $options = \array_merge_recursive($defaultOptions, $mandatoryOptions);
                $config  = $this->buildMultidimensionalArray($dimensions, $options);

                if ($key !== null && isset($configs[$key])) {
                    $config = \array_replace_recursive($configs[$key], $config);
                }

                $configs[$key] = $config;
            }
        }

        return $configs;
    }

    /**
     * Read the mandatory options and ask for the value.
     *
     * @param iterable $mandatoryOptions
     *
     * @return array
     */
    private function readMandatoryOption(iterable $mandatoryOptions): array
    {
        $options = [];

        foreach ($mandatoryOptions as $key => $mandatoryOption) {
            if (! \is_scalar($mandatoryOption)) {
                $options[$key] = $this->readMandatoryOption($mandatoryOptions[$key]);

                continue;
            }

            $options[$mandatoryOption] = $this->ask(\sprintf('Pleas enter the mandatory value for %s', $mandatoryOption));
        }

        return $options;
    }

    /**
     * Builds a multidimensional config array.
     *
     * @param iterable $dimensions
     * @param mixed    $value
     *
     * @return array
     */
    private function buildMultidimensionalArray(iterable $dimensions, $value): array
    {
        $config = [];
        $index  = \array_shift($dimensions);

        if (! isset($dimensions[0])) {
            $config[$index] = $value;
        } else {
            $config[$index] = $this->buildMultidimensionalArray($dimensions, $value);
        }

        return $config;
    }

    /**
     * Generate a config directory.
     *
     * @param string $dir
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    private static function generateDirectory(string $dir): void
    {
        if (\is_dir($dir) && \is_writable($dir)) {
            return;
        }

        if (! @\mkdir($dir, 0777, true) || ! \is_writable($dir)) {
            throw new InvalidArgumentException(sprintf(
                'Cache directory [%s] cannot be created or is write protected.',
                $dir
            ));
        }
    }
}
