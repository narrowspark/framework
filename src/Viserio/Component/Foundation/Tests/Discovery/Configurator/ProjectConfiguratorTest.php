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

/**
 * @internal
 */
final class ProjectConfiguratorTest extends MockeryTestCase
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

    public function testGetName(): void
    {
        $this->assertSame('narrowspark-project', ProjectConfigurator::getName());
    }

    /**
     * @param array $config
     */
    public function arrangeFullAndMicroProjectStructure(array $config): void
    {
        $this->arrangeAssertDirectoryExists($config);

        $this->assertDirectoryExists($config['app-dir'] . '/Console');
        $this->assertDirectoryExists($config['app-dir'] . '/Provider');
        $this->assertDirectoryExists($config['app-dir'] . '/Http/Middleware');
        $this->assertFileExists($config['app-dir'] . '/Http/Controller/Controller.php');

        $this->assertFileExists($config['routes-dir'] . '/api.php');
        $this->assertFileExists($config['routes-dir'] . '/console.php');
        $this->assertFileExists($config['routes-dir'] . '/web.php');

        $this->assertDirectoryExists($config['resources-dir'] . '/lang');
        $this->assertDirectoryExists($config['resources-dir'] . '/views');

        $this->assertFileExists($config['storage-dir'] . '/framework/.gitignore');
        $this->assertFileExists($config['storage-dir'] . '/logs/.gitignore');

        $this->assertDirectoryExists($config['tests-dir'] . '/Feature');
        $this->assertDirectoryExists($config['tests-dir'] . '/Unit');
        $this->assertFileExists($config['tests-dir'] . '/AbstractTestCase.php');
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

        $this->assertDirectoryExists($config['app-dir'] . '/Console');
        $this->assertDirectoryExists($config['app-dir'] . '/Provider');
        $this->assertDirectoryNotExists($config['app-dir'] . '/Http/Middleware');
        $this->assertFileNotExists($config['app-dir'] . '/Http/Controller/Controller.php');

        $this->assertFileNotExists($config['routes-dir'] . '/api.php');
        $this->assertFileExists($config['routes-dir'] . '/console.php');
        $this->assertFileNotExists($config['routes-dir'] . '/web.php');

        $this->assertDirectoryNotExists($this->path . '/resources/lang');
        $this->assertDirectoryNotExists($this->path . '/resources/views');

        $this->assertFileExists($config['storage-dir'] . '/framework/.gitignore');
        $this->assertFileExists($config['storage-dir'] . '/logs/.gitignore');

        $this->assertDirectoryNotExists($config['tests-dir'] . '/Feature');
        $this->assertDirectoryExists($config['tests-dir'] . '/Unit');
        $this->assertFileExists($config['tests-dir'] . '/AbstractTestCase.php');
    }

    public function testUnconfigure(): void
    {
        $configurator = new ProjectConfigurator($this->composer, $this->ioMock, []);
        $package      = $this->mock(PackageContract::class);

        $this->ioMock->shouldReceive('writeError')
            ->once()
            ->with(['    Project cant be unconfigured'], true, IOInterface::VERBOSE);

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

            $this->assertDirectoryExists($dir);
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
