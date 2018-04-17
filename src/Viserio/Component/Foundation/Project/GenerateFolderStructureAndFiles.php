<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Project;

use Composer\IO\IOInterface;
use Narrowspark\Discovery\Common\Traits\ExpandTargetDirTrait;
use Narrowspark\Discovery\Discovery;
use Symfony\Component\Filesystem\Filesystem;

final class GenerateFolderStructureAndFiles
{
    use ExpandTargetDirTrait;

    /**
     * Creates all need folders and files.
     *
     * @param array                    $options
     * @param string                   $projectType
     * @param \Composer\IO\IOInterface $io
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return void
     */
    public static function create(array $options, string $projectType, IOInterface $io): void
    {
        $filesystem = new Filesystem();

        $filesystem->mkdir(self::expandTargetDir($options, '%CONFIG_DIR%'));

        $io->writeError('Config folder created', true, IOInterface::VERBOSE);

        self::createStorageFolders($options, $filesystem, $io);
        self::createTestFolders($options, $filesystem, $projectType, $io);
        self::createRoutesFolder($options, $filesystem, $projectType, $io);
        self::createResourcesFolders($options, $filesystem, $projectType, $io);
        self::createAppFolders($options, $filesystem, $projectType, $io);
        self::removeFilesAndDirsOnProjectType($options, $filesystem, $projectType, $io);
    }

    /**
     * Removes files and dirs on project type.
     *
     * @param array                                    $options
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param string                                   $projectType
     * @param \Composer\IO\IOInterface                 $io
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return void
     */
    private static function removeFilesAndDirsOnProjectType(array $options, Filesystem $filesystem, string $projectType, IOInterface $io): void
    {
        $removes = [];

        if ($projectType === Discovery::CONSOLE_PROJECT) {
            $removes[] = self::expandTargetDir($options, '%PUBLIC_DIR%');
        } elseif ($projectType === Discovery::HTTP_PROJECT) {
            $removes[] = 'cerebro';
        }

        if (! isset($options['discovery_test']) && \file_exists('README.md')) {
            $removes[] = 'README.md';
        }

        $filesystem->remove($removes);
    }

    /**
     * Create storage folders.
     *
     * @param array                                    $options
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param \Composer\IO\IOInterface                 $io
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return void
     */
    private static function createStorageFolders(array $options, Filesystem $filesystem, IOInterface $io): void
    {
        $storagePath = self::expandTargetDir($options, '%STORAGE_DIR%');

        $storageFolders = [
            'storage'   => $storagePath,
            'logs'      => $storagePath . '/logs',
            'framework' => $storagePath . '/framework',
        ];

        $filesystem->mkdir($storageFolders);
        $filesystem->dumpFile($storageFolders['logs'] . '/.gitignore', "!.gitignore\n");
        $filesystem->dumpFile($storageFolders['framework'] . '/.gitignore', "down\n");

        $io->writeError('Storage folders created', true, IOInterface::VERBOSE);
    }

    /**
     * Create test folders.
     *
     * @param array                                    $options
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param string                                   $projectType
     * @param \Composer\IO\IOInterface                 $io
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return void
     */
    private static function createTestFolders(array $options, Filesystem $filesystem, string $projectType, IOInterface $io): void
    {
        $testsPath      = self::expandTargetDir($options, '%TESTS_DIR%');
        $testFolders    = [
            'tests' => $testsPath,
            'unit'  => $testsPath . '/Unit',
        ];
        $phpunitContent = \file_get_contents(__DIR__ . '/../Resource/phpunit.xml.template');

        if (\in_array($projectType, [Discovery::FULL_PROJECT, Discovery::HTTP_PROJECT], true)) {
            $testFolders['feature'] = $testsPath . '/Feature';

            $feature        = "        <testsuite name=\"Feature\">\n            <directory suffix=\"Test.php\">./tests/Feature</directory>\n        </testsuite>\n";
            $phpunitContent = self::doInsertStringBeforePosition(
                $phpunitContent,
                $feature,
                \mb_strpos($phpunitContent, '</testsuites>')
            );
        }

        $filesystem->mkdir($testFolders);

        $filesystem->dumpFile(
            $testFolders['tests'] . '/AbstractTestCase.php',
            \file_get_contents(__DIR__ . '/../Resource/AbstractTestCase.php.template')
        );

        $filesystem->dumpFile(
            $testFolders['tests'] . '/bootstrap.php',
            \file_get_contents(__DIR__ . '/../Resource/bootstrap.php.template')
        );

        if (! isset($options['discovery_test'])) {
            $filesystem->dumpFile('phpunit.xml', $phpunitContent);
        }

        $io->writeError('Tests folder created', true, IOInterface::VERBOSE);
    }

    /**
     * Create routes folder.
     *
     * @param array                                    $options
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param string                                   $projectType
     * @param \Composer\IO\IOInterface                 $io
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return void
     */
    private static function createRoutesFolder(array $options, Filesystem $filesystem, string $projectType, IOInterface $io): void
    {
        $routesPath = self::expandTargetDir($options, '%ROUTES_DIR%');

        $filesystem->mkdir($routesPath);

        if (\in_array($projectType, [Discovery::FULL_PROJECT, Discovery::HTTP_PROJECT], true)) {
            $filesystem->dumpFile(
                $routesPath . '/web.php',
                \file_get_contents(__DIR__ . '/../Resource/Routes/web.php.template')
            );
            $filesystem->dumpFile(
                $routesPath . '/api.php',
                \file_get_contents(__DIR__ . '/../Resource/Routes/api.php.template')
            );
        }

        if (\in_array($projectType, [Discovery::FULL_PROJECT, Discovery::CONSOLE_PROJECT], true)) {
            $filesystem->dumpFile(
                $routesPath . '/console.php',
                \file_get_contents(__DIR__ . '/../Resource/Routes/console.php.template')
            );
        }

        $io->writeError('Routes folder created', true, IOInterface::VERBOSE);
    }

    /**
     * Create resources folders.
     *
     * @param array                                    $options
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param string                                   $projectType
     * @param \Composer\IO\IOInterface                 $io
     *
     * @return void
     */
    private static function createResourcesFolders(array $options, Filesystem $filesystem, string $projectType, IOInterface $io): void
    {
        if (\in_array($projectType, [Discovery::FULL_PROJECT, Discovery::HTTP_PROJECT], true)) {
            $resourcesPath = self::expandTargetDir($options, '%RESOURCES_DIR%');

            $testFolders = [
                'resources' => $resourcesPath,
                'views'     => $resourcesPath . '/views',
                'lang'      => $resourcesPath . '/lang',
            ];

            $filesystem->mkdir($testFolders);

            $io->writeError('Resources folder created', true, IOInterface::VERBOSE);
        }
    }

    /**
     * Create app folders.
     *
     * @param array                                    $options
     * @param \Symfony\Component\Filesystem\Filesystem $filesystem
     * @param string                                   $projectType
     * @param \Composer\IO\IOInterface                 $io
     *
     * @throws \Symfony\Component\Filesystem\Exception\IOException
     *
     * @return void
     */
    private static function createAppFolders(array $options, Filesystem $filesystem, string $projectType, IOInterface $io): void
    {
        $appPath = self::expandTargetDir($options, '%APP_DIR%');

        $appFolders = [
            'app'      => $appPath,
            'provider' => $appPath . '/Provider',
        ];

        if (\in_array($projectType, [Discovery::FULL_PROJECT, Discovery::HTTP_PROJECT], true)) {
            $appFolders = \array_merge(
                $appFolders,
                [
                    'http'       => $appFolders['app'] . '/Http',
                    'controller' => $appFolders['app'] . '/Http/Controller',
                    'middleware' => $appFolders['app'] . '/Http/Middleware',
                ]
            );
            $filesystem->dumpFile(
                $appFolders['controller'] . '/Controller.php',
                \file_get_contents(__DIR__ . '/../Resource/Http/Controller.php.template')
            );
        }

        if (\in_array($projectType, [Discovery::FULL_PROJECT, Discovery::CONSOLE_PROJECT], true)) {
            $appFolders['console'] = $appFolders['app'] . '/Console';
        }

        $filesystem->mkdir($appFolders);

        $io->writeError('App folder created', true, IOInterface::VERBOSE);
    }

    /**
     * Insert string at specified position.
     *
     * @param string $string
     * @param string $insertStr
     * @param int    $position
     *
     * @return string
     */
    private static function doInsertStringBeforePosition(string $string, string $insertStr, int $position): string
    {
        return \mb_substr($string, 0, $position) . $insertStr . \mb_substr($string, $position);
    }
}
