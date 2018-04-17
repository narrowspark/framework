<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Project;

use Composer\IO\IOInterface;
use Narrowspark\Discovery\Common\Contract\Discovery as DiscoveryContract;
use Narrowspark\Discovery\Common\Traits\ExpandTargetDirTrait;

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
     * @return void
     */
    public static function create(array $options, string $projectType, IOInterface $io): void
    {
        \mkdir(self::expandTargetDir($options, '%CONFIG_DIR%'));

        self::createStorageFolders($options);
        self::createTestFolders($options, $projectType);
        self::createRoutesFolder($options, $projectType);
        self::createResourcesFolders($options, $projectType);
        self::createAppFolders($options, $projectType);
        self::createFoundationFilesAndFolders($options, $projectType);

        if (! isset($options['discovery_test']) && \file_exists('README.md')) {
            \unlink('README.md');
        }

        $io->writeError('Project directories and files created', true, IOInterface::VERBOSE);
    }

    /**
     * Create storage folders.
     *
     * @param array $options
     *
     * @return void
     */
    private static function createStorageFolders(array $options): void
    {
        $storagePath = self::expandTargetDir($options, '%STORAGE_DIR%');

        $storageFolders = [
            'storage'   => $storagePath,
            'logs'      => $storagePath . '/logs',
            'framework' => $storagePath . '/framework',
        ];

        self::mkdir($storageFolders);

        \file_put_contents($storageFolders['logs'] . '/.gitignore', "!.gitignore\n");
        \file_put_contents($storageFolders['framework'] . '/.gitignore', "down\n");
    }

    /**
     * Create test folders.
     *
     * @param array  $options
     * @param string $projectType
     *
     * @return void
     */
    private static function createTestFolders(array $options, string $projectType): void
    {
        $testsPath      = self::expandTargetDir($options, '%TESTS_DIR%');
        $testFolders    = [
            'tests' => $testsPath,
            'unit'  => $testsPath . '/Unit',
        ];
        $phpunitContent = \file_get_contents(__DIR__ . '/../Resource/phpunit.xml.template');

        if (\in_array($projectType, [DiscoveryContract::FULL_PROJECT, DiscoveryContract::HTTP_PROJECT], true)) {
            $testFolders['feature'] = $testsPath . '/Feature';

            $feature        = "        <testsuite name=\"Feature\">\n            <directory suffix=\"Test.php\">./tests/Feature</directory>\n        </testsuite>\n";
            $phpunitContent = self::doInsertStringBeforePosition($phpunitContent, $feature, \mb_strpos($phpunitContent, '</testsuites>'));
        }

        self::mkdir($testFolders);

        self::dumpFile($testFolders['tests'] . '/AbstractTestCase.php', 'AbstractTestCase.php.template');
        self::dumpFile($testFolders['tests'] . '/bootstrap.php', 'bootstrap.php.template');

        if (! isset($options['discovery_test'])) {
            self::dumpFile('phpunit.xml', $phpunitContent);
        }
    }

    /**
     * Create routes folder.
     *
     * @param array  $options
     * @param string $projectType
     *
     * @return void
     */
    private static function createRoutesFolder(array $options, string $projectType): void
    {
        $routesPath = self::expandTargetDir($options, '%ROUTES_DIR%');

        self::mkdir($routesPath);

        if (\in_array($projectType, [DiscoveryContract::FULL_PROJECT, DiscoveryContract::HTTP_PROJECT], true)) {
            self::dumpFile($routesPath . '/web.php', 'Routes/web.php.template');
            self::dumpFile($routesPath . '/api.php', 'Routes/api.php.template');
        }

        if (\in_array($projectType, [DiscoveryContract::FULL_PROJECT, DiscoveryContract::CONSOLE_PROJECT], true)) {
            self::dumpFile($routesPath . '/console.php', 'Routes/console.php.template');
        }
    }

    /**
     * Create resources folders.
     *
     * @param array  $options
     * @param string $projectType
     *
     * @return void
     */
    private static function createResourcesFolders(array $options, string $projectType): void
    {
        if (\in_array($projectType, [DiscoveryContract::FULL_PROJECT, DiscoveryContract::HTTP_PROJECT], true)) {
            $resourcesPath = self::expandTargetDir($options, '%RESOURCES_DIR%');

            $testFolders = [
                'resources' => $resourcesPath,
                'views'     => $resourcesPath . '/views',
                'lang'      => $resourcesPath . '/lang',
            ];

            self::mkdir($testFolders);
        }
    }

    /**
     * Create app folders.
     *
     * @param array  $options
     * @param string $projectType
     *
     * @return void
     */
    private static function createAppFolders(array $options, string $projectType): void
    {
        $appPath = self::expandTargetDir($options, '%APP_DIR%');

        self::mkdir(['app' => $appPath, 'provider' => $appPath . '/Provider']);

        if (\in_array($projectType, [DiscoveryContract::FULL_PROJECT, DiscoveryContract::HTTP_PROJECT], true)) {
            $appFolders = [
                'http'       => $appPath . '/Http',
                'controller' => $appPath . '/Http/Controller',
                'middleware' => $appPath . '/Http/Middleware',
            ];

            self::mkdir($appFolders);
            self::dumpFile($appFolders['controller'] . '/Controller.php', 'Http/Controller.php.template');
        }

        if (\in_array($projectType, [DiscoveryContract::FULL_PROJECT, DiscoveryContract::CONSOLE_PROJECT], true)) {
            self::mkdir($appPath . '/Console');
        }
    }

    /**
     * Creates dirs and files for foundation.
     *
     * @param array  $options
     * @param string $projectType
     *
     * @return void
     */
    private static function createFoundationFilesAndFolders(array $options, string $projectType): void
    {
        if (\in_array($projectType, [DiscoveryContract::FULL_PROJECT, DiscoveryContract::HTTP_PROJECT], true)) {
            $publicPath = self::expandTargetDir($options, '%PUBLIC_DIR%');

            self::mkdir($publicPath);
            self::dumpFile($publicPath . '/index.php', 'index.php.template');
        }

        if (! isset($options['discovery_test']) &&
            \in_array($projectType, [DiscoveryContract::FULL_PROJECT, DiscoveryContract::CONSOLE_PROJECT], true)
        ) {
            self::dumpFile('cerebro', 'cerebro.template');
        }
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

    /**
     * Creates a directory recursively.
     *
     * @param string $filename
     * @param string $contentPath
     *
     * @return void
     */
    private static function dumpFile(string $filename, string $contentPath): void
    {
        \file_put_contents($filename, \file_get_contents(__DIR__ . '/../Resource/' . $contentPath));
    }

    /**
     * Creates a directory recursively.
     *
     * @param array|string $folders
     *
     * @return void
     */
    private static function mkdir($folders): void
    {
        foreach ((array) $folders as $folder) {
            if (\is_dir($folder)) {
                continue;
            }

            \mkdir($folder, 0777, true);
        }
    }
}
