<?php
declare(strict_types=1);
namespace Viserio\Component\Foundation\Tests\Discovery\Configurator;

use Composer\Composer;
use Composer\IO\IOInterface;
use Narrowspark\Discovery\Common\Contract\Package as PackageContract;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Viserio\Component\Foundation\Discovery\Configurator\ProjectConfigurator;
use Viserio\Component\Support\Traits\NormalizePathAndDirectorySeparatorTrait;

class ProjectConfiguratorTest extends MockeryTestCase
{
    use NormalizePathAndDirectorySeparatorTrait;

    /**
     * @var \Composer\Composer
     */
    private $composer;

    /**
     * @var \Composer\IO\IOInterface|\Mockery\MockInterface
     */
    private $ioMock;

    /**
     * @var \Viserio\Component\Foundation\Discovery\Configurator\ProjectConfigurator
     */
    private $configurator;

    /**
     * @var string
     */
    private $path;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->composer = new Composer();
        $this->ioMock   = $this->mock(IOInterface::class);

        $this->path = self::normalizeDirectorySeparator(__DIR__ . '/GenerateFolderStructureAndFilesTest');

        @\mkdir($this->path);

        ProjectConfigurator::$isTest = true;

        $this->configurator = new ProjectConfigurator($this->composer, $this->ioMock, $this->arrangeConfig());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem())->remove(self::normalizeDirectorySeparator($this->path));
    }

    /**
     * @param $config
     */
    public function arrangeFullAndMicroProjectStructure($config): void
    {
        $this->arrangeAssertDirectoryExists($config);

        self::assertDirectoryExists($config['app-dir'] . '/Console');
        self::assertDirectoryExists($config['app-dir'] . '/Provider');
        self::assertDirectoryExists($config['app-dir'] . '/Http/Middleware');
        self::assertFileExists($config['app-dir'] . '/Http/Controller/Controller.php');

        self::assertFileExists($config['routes-dir'] . '/api.php');
        self::assertFileExists($config['routes-dir'] . '/console.php');
        self::assertFileExists($config['routes-dir'] . '/web.php');

        self::assertDirectoryExists($config['resources-dir'] . '/lang');
        self::assertDirectoryExists($config['resources-dir'] . '/views');

        self::assertFileExists($config['storage-dir'] . '/framework/.gitignore');
        self::assertFileExists($config['storage-dir'] . '/logs/.gitignore');

        self::assertDirectoryExists($config['tests-dir'] . '/Feature');
        self::assertDirectoryExists($config['tests-dir'] . '/Unit');
        self::assertFileExists($config['tests-dir'] . '/AbstractTestCase.php');
    }

    public function testConfigureWithFullProjectType(): void
    {
        $config  = $this->arrangeConfig();
        $package = $this->mock(PackageContract::class);

        $this->ioMock->shouldReceive('askAndValidate')
            ->once()
            ->andReturn('f');
        $this->ioMock->shouldReceive('writeError')
            ->once()
            ->with(['    Creating project directories and files'], true, IOInterface::VERBOSE);

        $this->configurator->configure($package);

        $this->arrangeFullAndMicroProjectStructure($config);
    }

    public function testCreateWithMicroProjectType(): void
    {
        $config  = $this->arrangeConfig();
        $package = $this->mock(PackageContract::class);

        $this->ioMock->shouldReceive('askAndValidate')
            ->once()
            ->andReturn('m');
        $this->ioMock->shouldReceive('writeError')
            ->once()
            ->with(['    Creating project directories and files'], true, IOInterface::VERBOSE);

        $this->configurator->configure($package);

        $this->arrangeFullAndMicroProjectStructure($config);
    }

    public function testCreateWithConsoleProjectType(): void
    {
        $config  = $this->arrangeConfig();
        $package = $this->mock(PackageContract::class);

        $this->ioMock->shouldReceive('askAndValidate')
            ->once()
            ->andReturn('c');
        $this->ioMock->shouldReceive('writeError')
            ->once()
            ->with(['    Creating project directories and files'], true, IOInterface::VERBOSE);

        $this->configurator->configure($package);

        $this->arrangeAssertDirectoryExists($config, ['resources-dir', 'public-dir']);

        self::assertDirectoryExists($config['app-dir'] . '/Console');
        self::assertDirectoryExists($config['app-dir'] . '/Provider');
        self::assertDirectoryNotExists($config['app-dir'] . '/Http/Middleware');
        self::assertFileNotExists($config['app-dir'] . '/Http/Controller/Controller.php');

        self::assertFileNotExists($config['routes-dir'] . '/api.php');
        self::assertFileExists($config['routes-dir'] . '/console.php');
        self::assertFileNotExists($config['routes-dir'] . '/web.php');

        self::assertDirectoryNotExists($this->path . '/resources/lang');
        self::assertDirectoryNotExists($this->path . '/resources/views');

        self::assertFileExists($config['storage-dir'] . '/framework/.gitignore');
        self::assertFileExists($config['storage-dir'] . '/logs/.gitignore');

        self::assertDirectoryNotExists($config['tests-dir'] . '/Feature');
        self::assertDirectoryExists($config['tests-dir'] . '/Unit');
        self::assertFileExists($config['tests-dir'] . '/AbstractTestCase.php');
    }

    public function testUnconfigure(): void
    {
        $configurator = new ProjectConfigurator($this->composer, $this->ioMock, []);
        $package      = $this->mock(PackageContract::class);

        $this->ioMock->shouldReceive('writeError')
            ->once()
            ->with(['    Project cant be unconfigure'], true, IOInterface::VERBOSE);

        $configurator->unconfigure($package);
    }

    /**
     * @param array $config
     * @param array $skip
     */
    protected function arrangeAssertDirectoryExists(array $config, array $skip = []): void
    {
        foreach ($config as $key => $dir) {
            if (\in_array($key, $skip, true)) {
                continue;
            }

            self::assertDirectoryExists($dir);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods($allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }

    /**
     * @return array
     */
    private function arrangeConfig(): array
    {
        return [
            'app-dir'        => $this->path . '/app',
            'public-dir'     => $this->path . '/public',
            'resources-dir'  => $this->path . '/resources',
            'routes-dir'     => $this->path . '/routes',
            'tests-dir'      => $this->path . '/tests',
            'storage-dir'    => $this->path . '/storage',
        ];
    }
}
