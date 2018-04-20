<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Discovery\Configurator;

use Composer\Composer;
use Composer\IO\IOInterface;
use Narrowspark\Discovery\Common\Configurator\AbstractConfigurator;
use Narrowspark\Discovery\Common\Contract\Package as PackageContract;
use Narrowspark\Discovery\Common\Exception\InvalidArgumentException;

class ProjectConfigurator extends AbstractConfigurator
{
    /**
     * @var string
     */
    private const FULL_PROJECT = 'full';

    /**
     * @var string
     */
    private const MICRO_PROJECT = 'http';

    /**
     * @var string
     */
    private const CONSOLE_PROJECT = 'console';

    /**
     * This should be only used if this class is tested.
     *
     * @internal
     *
     * @var bool
     */
    public static $isTest = false;

    /**
     * Path to the resource dir.
     *
     * @var string
     */
    private $resourcePath;

    /**
     * @var string
     */
    private static $question = '    Please choose you project type.
    [<comment>c</comment>] console-framework
    [<comment>f</comment>] full-stack framework
    [<comment>m</comment>] micro-framework
    (defaults to <comment>f</comment>): ';

    /**
     * {@inheritdoc}
     */
    public function __construct(Composer $composer, IOInterface $io, array $options = [])
    {
        parent::__construct($composer, $io, $options);

        $this->resourcePath = __DIR__ . '/../../Resource';
    }

    /**
     * {@inheritdoc}
     */
    public function configure(PackageContract $package): void
    {
        $answer = $this->io->askAndValidate(
            self::$question,
            [$this, 'validateProjectQuestionAnswerValue'],
            null,
            'f'
        );
        $mapping = [
            'f' => self::FULL_PROJECT,
            'c' => self::CONSOLE_PROJECT,
            'm' => self::MICRO_PROJECT,
        ];

        $this->write('Creating project directories and files');

        $this->createStorageFolders();
        $this->createTestFolders($mapping[$answer]);
        $this->createRoutesFolder($mapping[$answer]);
        $this->createResourcesFolders($mapping[$answer]);
        $this->createAppFolders($mapping[$answer]);
        $this->createFoundationFilesAndFolders($mapping[$answer]);

        if (! self::$isTest && \file_exists('README.md')) {
            \unlink('README.md');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unconfigure(PackageContract $package): void
    {
        $this->write('Project cant be unconfigure');
    }

    /**
     * Validate given input answer.
     *
     * @param null|string $value
     *
     * @throws \Narrowspark\Discovery\Common\Exception\InvalidArgumentException
     *
     * @return string
     */
    public function validateProjectQuestionAnswerValue(?string $value): string
    {
        if ($value === null) {
            return 'f';
        }

        $value = \mb_strtolower($value[0]);

        if (! \in_array($value, ['f', 'm', 'c'], true)) {
            throw new InvalidArgumentException('Invalid choice.');
        }

        return $value;
    }

    /**
     * Create storage folders.
     *
     * @return void
     */
    private function createStorageFolders(): void
    {
        $storagePath = self::expandTargetDir($this->options, '%STORAGE_DIR%');

        $storageFolders = [
            'storage'   => $storagePath,
            'logs'      => $storagePath . '/logs',
            'framework' => $storagePath . '/framework',
        ];

        $this->filesystem->mkdir($storageFolders);
        $this->filesystem->dumpFile($storageFolders['logs'] . '/.gitignore', "!.gitignore\n");
        $this->filesystem->dumpFile($storageFolders['framework'] . '/.gitignore', "down\n");
    }

    /**
     * Create test folders.
     *
     * @param string $projectType
     *
     * @return void
     */
    private function createTestFolders(string $projectType): void
    {
        $testsPath      = self::expandTargetDir($this->options, '%TESTS_DIR%');
        $testFolders    = [
            'tests' => $testsPath,
            'unit'  => $testsPath . '/Unit',
        ];
        $phpunitContent = \file_get_contents($this->resourcePath . '/phpunit.xml.template');

        if (\in_array($projectType, [self::FULL_PROJECT, self::MICRO_PROJECT], true)) {
            $testFolders['feature'] = $testsPath . '/Feature';

            $feature        = "        <testsuite name=\"Feature\">\n            <directory suffix=\"Test.php\">./tests/Feature</directory>\n        </testsuite>\n";
            $phpunitContent = $this->doInsertStringBeforePosition($phpunitContent, $feature, \mb_strpos($phpunitContent, '</testsuites>'));
        }

        $this->filesystem->mkdir($testFolders);

        $this->filesystem->copy($this->resourcePath . '/AbstractTestCase.php.template', $testFolders['tests'] . '/AbstractTestCase.php');
        $this->filesystem->copy($this->resourcePath . '/bootstrap.php.template', $testFolders['tests'] . '/bootstrap.php');

        if (! self::$isTest) {
            $this->filesystem->dumpFile('phpunit.xml', $phpunitContent);
        }
    }

    /**
     * Create routes folder.
     *
     * @param string $projectType
     *
     * @return void
     */
    private function createRoutesFolder(string $projectType): void
    {
        $routesPath = self::expandTargetDir($this->options, '%ROUTES_DIR%');

        if (\in_array($projectType, [self::FULL_PROJECT, self::MICRO_PROJECT], true)) {
            $this->filesystem->copy($this->resourcePath . '/Routes/web.php.template', $routesPath . '/web.php');
            $this->filesystem->copy($this->resourcePath . '/Routes/api.php.template', $routesPath . '/api.php');
        }

        if (\in_array($projectType, [self::FULL_PROJECT, self::MICRO_PROJECT, self::CONSOLE_PROJECT], true)) {
            $this->filesystem->copy($this->resourcePath . '/Routes/console.php.template', $routesPath . '/console.php');
        }
    }

    /**
     * Create resources folders.
     *
     * @param string $projectType
     *
     * @return void
     */
    private function createResourcesFolders(string $projectType): void
    {
        if (\in_array($projectType, [self::FULL_PROJECT, self::MICRO_PROJECT], true)) {
            $resourcesPath = self::expandTargetDir($this->options, '%RESOURCES_DIR%');

            $testFolders = [
                'resources' => $resourcesPath,
                'views'     => $resourcesPath . '/views',
                'lang'      => $resourcesPath . '/lang',
            ];

            $this->filesystem->mkdir($testFolders);
        }
    }

    /**
     * Create app folders.
     *
     * @param string $projectType
     *
     * @return void
     */
    private function createAppFolders(string $projectType): void
    {
        $appPath = self::expandTargetDir($this->options, '%APP_DIR%');

        $this->filesystem->mkdir(['app' => $appPath, 'provider' => $appPath . '/Provider']);

        if (\in_array($projectType, [self::FULL_PROJECT, self::MICRO_PROJECT], true)) {
            $appFolders = [
                'http'       => $appPath . '/Http',
                'controller' => $appPath . '/Http/Controller',
                'middleware' => $appPath . '/Http/Middleware',
            ];

            $this->filesystem->mkdir($appFolders);
            $this->filesystem->copy($this->resourcePath . '/Http/Controller.php.template', $appFolders['controller'] . '/Controller.php');
        }

        if (\in_array($projectType, [self::FULL_PROJECT, self::MICRO_PROJECT, self::CONSOLE_PROJECT], true)) {
            $this->filesystem->mkdir($appPath . '/Console');
        }
    }

    /**
     * Creates dirs and files for foundation.
     *
     * @param string $projectType
     *
     * @return void
     */
    private function createFoundationFilesAndFolders(string $projectType): void
    {
        if (\in_array($projectType, [self::FULL_PROJECT, self::MICRO_PROJECT], true)) {
            $publicPath = self::expandTargetDir($this->options, '%PUBLIC_DIR%');

            $this->filesystem->copy($this->resourcePath . '/index.php.template', $publicPath . '/index.php');
        }

        if (! self::$isTest && \in_array($projectType, [self::FULL_PROJECT, self::MICRO_PROJECT, self::CONSOLE_PROJECT], true)) {
            $this->filesystem->copy($this->resourcePath . '/cerebro.template', 'cerebro');
        }
    }
}
