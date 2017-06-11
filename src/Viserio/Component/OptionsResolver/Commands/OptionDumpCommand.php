<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Commands;

use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Parsers\Dumper;
use Viserio\Component\Support\Traits\ArrayPrettyPrintTrait;

class OptionDumpCommand extends Command
{
    use ArrayPrettyPrintTrait;

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
        $configs = $this->getOptionsFromDeclaredClasses();
        $format  = $this->option('format');
        $dirPath = $this->argument('dir');
        $dumper  = null;

        if ($this->container !== null && $this->getContainer()->has(Dumper::class)) {
            $dumper = $this->getContainer()->get(Dumper::class);
        }

        if ($dumper === null && $format !== 'php') {
            $this->error('Only the php format is supported; use composer req viserio/parsers to get json, xml, yml output.');

            return;
        }

        if (! is_dir($dirPath)) {
            mkdir($dirPath);
        }

        foreach ($configs as $key => $config) {
            $content = '';
            $file    = $dirPath . '\\' . $key . '.' . $format;

            if ($dumper !== null) {
                $content = $dumper->dump($config, $format);
            } else {
                $content  = '<?php
declare(strict_types=1);

return ';
                $content .= $this->prettyPrintArray($config);
                $content .= ';';
            }

            if ($this->hasOption('overwrite') || ! file_exists($file)) {
                file_put_contents($file, $content);
            } else {
                $confirmed = $this->confirm(sprintf('Do you really wish to overwrite %s', $key));

                if ($confirmed) {
                    file_put_contents($file, $content);
                } else {
                    continue;
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments(): array
    {
        return [
            [
                'dir',
                InputArgument::REQUIRED,
                'Path to the config dir.',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions(): array
    {
        return [
            [
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'The output format (php, json, xml, json)',
                'php',
            ],
            [
                'overwrite',
                null,
                InputOption::VALUE_NONE,
                'Overwrite existent config',
            ],
        ];
    }

    /**
     * Return a array full of declared class options.
     *
     * @return array
     */
    private function getOptionsFromDeclaredClasses(): array
    {
        $configs = [];

        foreach (get_declared_classes() as $className) {
            $reflectionClass = new ReflectionClass($className);
            $interfaces      = $reflectionClass->getInterfaceNames();

            if (! $reflectionClass->isInternal() && ! $reflectionClass->isAbstract() && in_array(RequiresConfigContract::class, $interfaces, true)) {
                $factory          = $reflectionClass->newInstanceWithoutConstructor();
                $dimensions       = [];
                $mandatoryOptions = [];
                $defaultOptions   = [];
                $key              = null;

                if (in_array(RequiresComponentConfigContract::class, $interfaces, true)) {
                    $dimensions = (array) $factory->getDimensions();
                    $key        = end($dimensions);
                }

                if (in_array(ProvidesDefaultOptionsContract::class, $interfaces, true)) {
                    $defaultOptions = (array) $factory->getDefaultOptions();
                }

                if (in_array(RequiresMandatoryOptionsContract::class, $interfaces, true)) {
                    $mandatoryOptions = $this->readMandatoryOption($factory->getMandatoryOptions());
                }

                $options = array_merge_recursive($defaultOptions, $mandatoryOptions);
                $config  = $this->buildMultidimensionalArray($dimensions, $options);

                if ($key !== null && isset($configs[$key])) {
                    $config = array_replace_recursive($configs[$key], $config);
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
            if (! is_scalar($mandatoryOption)) {
                $options[$key] = $this->readMandatoryOption($mandatoryOptions[$key]);

                continue;
            }

            $options[$mandatoryOption] = $this->ask(sprintf('Pleas enter the mandatory value for %s', $mandatoryOption));
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
        $index  = array_shift($dimensions);

        if (! isset($dimensions[0])) {
            $config[$index] = $value;
        } else {
            $config[$index] = $this->buildMultidimensionalArray($dimensions, $value);
        }

        return $config;
    }
}
