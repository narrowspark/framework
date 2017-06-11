<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Commands;

use ReflectionClass;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Traversable;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Parsers\Dumper;

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
        $configs = $this->getOptionsFromDeclaredClasses();
        $format  = $this->option('format');
        $dirPath = $this->argument('dir');
        $dumper  = null;

        if ($this->container !== null && $this->getContainer()->has(Dumper::class)) {
            $dumper = $this->getContainer()->get(Dumper::class);
        }

        if ($dumper === null && $format !== 'php') {
            $this->error('Only php format is supported; use composer req viserio/parsers to get json, xml, toml output.');
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
                $content .= $this->prepareConfig($config);
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
     * Prepare config for save.
     *
     * @param iterable $config
     * @param in       $indentLevel
     *
     * @return string
     */
    private function prepareConfig(iterable $config, int $indentLevel = 1): string
    {
        $indent  = str_repeat(' ', $indentLevel * 4);
        $entries = [];

        foreach ($config as $key => $value) {
            if (! is_int($key)) {
                if (is_string($key) && class_exists($key) && ctype_upper($key[0])) {
                    $key = sprintf('\\%s::class', ltrim($key, '\\'));
                } else {
                    $key = sprintf("'%s'", $key);
                }
            }

            $entries[] = sprintf(
                '%s%s%s,',
                $indent,
                sprintf('%s => ', $key),
                $this->createConfigValue($value, $indentLevel)
            );
        }

        $outerIndent = str_repeat(' ', ($indentLevel - 1) * 4);

        return sprintf("[\n%s\n%s]", implode("\n", $entries), $outerIndent);
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

    /**
     * Create the right value.
     *
     * @param mixed $value
     * @param int   $indentLevel
     *
     * @return string|int|float
     */
    private function createConfigValue($value, int $indentLevel)
    {
        if (is_array($value) || $value instanceof Traversable) {
            return $this->prepareConfig($value, $indentLevel + 1);
        }

        if (is_string($value) && class_exists($value) && ctype_upper($value[0])) {
            return sprintf('\\%s::class', ltrim($value, '\\'));
        }

        if (is_numeric($value)) {
            return $value + 0;
        }

        return var_export($value, true);
    }
}
