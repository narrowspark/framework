<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Command;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionException;
use ReflectionObject;
use RegexIterator;
use SplFileObject;
use Symfony\Component\Console\Helper\ProgressBar;
use Viserio\Component\Console\Command\Command;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException;
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
    protected static $defaultName = 'option:dump';

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
     * Root dir path.
     *
     * @var string
     */
    private $rootDir;

    /**
     * {@inheritdoc}
     *
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException if dir cant be created or is not writable
     */
    public function handle()
    {
        $dirPath = $this->argument('dir');

        if ($dirPath === null) {
            $this->error('Argument [dir] can\'t be empty.');

            return 1;
        }

        $format = $this->option('format');
        $dumper = null;

        if ($this->container !== null && $this->getContainer()->has(Dumper::class)) {
            $dumper = $this->getContainer()->get(Dumper::class);
        }

        if ($dumper === null && $format !== 'php') {
            $this->error('Only the php format is supported; use composer req viserio/parser to get [json], [xml], [yml] output.');

            return 1;
        }

        self::generateDirectory($dirPath);

        foreach ($this->getOptionsFromDeclaredClasses() as $key => $config) {
            $file = $dirPath . '\\' . $key . '.' . $format;

            if ($this->hasOption('merge') && \file_exists($file)) {
                $existingConfig = includeFile($file);
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
                $this->info("Output array:\n\n" . $content);

                if ($this->confirm(\sprintf('Write content to "%s"?', $file)) === false) {
                    continue;
                }
            }

            $this->putContentToFile($file, $content, $key);
        }

        return 0;
    }

    /**
     * Returns the composer vendor path.
     *
     * @return string
     */
    protected function getComposerVendorPath(): string
    {
        if ($this->rootDir === null) {
            $reflection = new ReflectionObject($this);
            $dir        = \dirname($reflection->getFileName());

            while (! \is_dir($dir . '/vendor/composer')) {
                $dir = \dirname($dir);
            }

            $this->rootDir = $dir;
        }

        return $this->rootDir . '/vendor/composer/';
    }

    /**
     * Return a array full of declared class options.
     *
     * @return array
     */
    private function getOptionsFromDeclaredClasses(): array
    {
        $configs = [];

        foreach ($this->getClassMap() as $className) {
            try {
                $reflectionClass = new ReflectionClass($className);
            } catch (ReflectionException $e) {
                continue;
            }

            $interfaces = \array_flip($reflectionClass->getInterfaceNames());

            if (isset($interfaces[RequiresConfigContract::class]) && ! $reflectionClass->isInternal() && ! $reflectionClass->isAbstract()) {
                $dimensions       = [];
                $mandatoryOptions = [];
                $defaultOptions   = [];
                $key              = null;

                if (isset($interfaces[RequiresComponentConfigContract::class])) {
                    $dimensions = (array) $className::getDimensions();
                    $key        = \end($dimensions);
                }

                if (isset($interfaces[ProvidesDefaultOptionsContract::class])) {
                    $defaultOptions = (array) $className::getDefaultOptions();
                }

                if (isset($interfaces[RequiresMandatoryOptionsContract::class])) {
                    $mandatoryOptions = $this->readMandatoryOption($className, $dimensions, $className::getMandatoryOptions());
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
     * @param string   $className
     * @param array    $dimensions
     * @param iterable $mandatoryOptions
     *
     * @return array
     */
    private function readMandatoryOption(string $className, array $dimensions, iterable $mandatoryOptions): array
    {
        $options = [];

        foreach ($mandatoryOptions as $key => $mandatoryOption) {
            if (! \is_scalar($mandatoryOption)) {
                $options[$key] = $this->readMandatoryOption($className, $dimensions, $mandatoryOptions[$key]);

                continue;
            }

            $options[$mandatoryOption] = $this->ask(
                \sprintf(
                    '%s: Please enter the following mandatory value for [%s]',
                    $className,
                    implode('.', $dimensions) . '.' . $mandatoryOption
                )
            );
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
     * @throws \Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException
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
                'Config directory [%s] cannot be created or is write protected.',
                $dir
            ));
        }
    }

    /**
     * @return array
     */
    private function getClassMap(): array
    {
        $classMap = \array_keys((array) require $this->getComposerVendorPath() . '/autoload_classmap.php');

        $output = $this->getOutput();
        $output->writeln(
            \sprintf(
            'Searching for php classes with implemented \%s interface.',
            RequiresConfigContract::class
        )
        );

        $splObjects = $this->getSplFileObjects();

        $progress = new ProgressBar($output, count($splObjects));
        $progress->start();

        foreach ($splObjects as $splObject) {
            $content   = \file_get_contents($splObject->getPathname());
            $tokens    = \token_get_all($content);
            $namespace = '';

            for ($index = 0; isset($tokens[$index]); $index++) {
                if (! isset($tokens[$index][0])) {
                    continue;
                }

                if (isset($tokens[$index][0]) && $tokens[$index][0] === T_NAMESPACE) {
                    $index += 2; // Skip namespace keyword and whitespace

                    while (isset($tokens[$index]) && \is_array($tokens[$index])) {
                        $namespace .= $tokens[$index++][1];
                    }
                }

                if (isset($tokens[$index][0]) && $tokens[$index][0] === T_CLASS && $tokens[$index - 1][0] !== T_DOUBLE_COLON) {
                    $index += 2; // Skip class keyword and whitespace

                    if (! \is_array($tokens[$index])) {
                        continue;
                    }

                    $class = \ltrim($namespace . '\\' . $tokens[$index][1], '\\');

                    if (! class_exists($class, false)) {
                        continue;
                    }

                    $classMap[] = $class;
                }
            }

            $progress->advance();
        }

        $progress->finish();

        $output->writeln('');

        return $classMap;
    }

    /**
     * Get all found classes as spl file objects.
     *
     *
     * @return array
     */
    private function getSplFileObjects(): array
    {
        $composerFolder = $this->getComposerVendorPath();
        $phpFilePaths   = array_merge(
            \array_values((array) require $composerFolder . '/autoload_psr4.php'),
            \array_values((array) require $composerFolder . '/autoload_namespaces.php')
        );

        $filesPaths = \array_values((array) require $composerFolder . '/autoload_files.php');

        $splObjects = [];

        foreach ($filesPaths as $path) {
            $splObjects[] = new SplFileObject($path);
        }

        foreach ($phpFilePaths as $path) {
            if (\is_array($path)) {
                foreach ($path as $subpath) {
                    $splObjects = \array_merge($splObjects, $this->getRegexIterator($subpath));
                }
            } else {
                $splObjects = \array_merge($splObjects, $this->getRegexIterator($path));
            }
        }

        return $splObjects;
    }

    /**
     * Returns a configured RegexIterator.
     *
     * @param string $path
     *
     * @return array
     */
    private function getRegexIterator(string $path): array
    {
        return \iterator_to_array(new RegexIterator(
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path)
            ),
            '/\.php$/'
        ));
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
