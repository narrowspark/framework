<?php
//declare(strict_types=1);
//namespace Viserio\Component\Foundation\Test\Project;
//
//use Composer\IO\NullIO;
//use Narrowspark\Discovery\Common\Contract\Discovery as DiscoveryContract;
//use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
//use Symfony\Component\Filesystem\Filesystem;
//use Viserio\Component\Foundation\Project\GenerateFolderStructureAndFiles;
//
//class GenerateFolderStructureAndFilesTest extends MockeryTestCase
//{
//    /**
//     * @var string
//     */
//    private $path;
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function setUp(): void
//    {
//        parent::setUp();
//
//        $this->path = __DIR__ . '/GenerateFolderStructureAndFilesTest';
//
//        @\mkdir($this->path);
//    }
//
//    /**
//     * {@inheritdoc}
//     */
//    protected function tearDown(): void
//    {
//        parent::tearDown();
//
//        (new Filesystem())->remove($this->path);
//    }
//
//    public function testCreateWithFullProjectType(): void
//    {
//        $config = $this->arrangeConfig();
//
//        GenerateFolderStructureAndFiles::create($config, DiscoveryContract::FULL_PROJECT, new NullIO());
//
//        $this->arrangeAssertDirectoryExists($config, []);
//
//        self::assertDirectoryExists($config['app-dir'] . '/Console');
//        self::assertDirectoryExists($config['app-dir'] . '/Provider');
//        self::assertDirectoryExists($config['app-dir'] . '/Http/Middleware');
//        self::assertFileExists($config['app-dir'] . '/Http/Controller/Controller.php');
//
//        self::assertFileExists($config['routes-dir'] . '/api.php');
//        self::assertFileExists($config['routes-dir'] . '/console.php');
//        self::assertFileExists($config['routes-dir'] . '/web.php');
//
//        self::assertDirectoryExists($config['resources-dir'] . '/lang');
//        self::assertDirectoryExists($config['resources-dir'] . '/views');
//
//        self::assertFileExists($config['storage-dir'] . '/framework/.gitignore');
//        self::assertFileExists($config['storage-dir'] . '/logs/.gitignore');
//
//        self::assertDirectoryExists($config['tests-dir'] . '/Feature');
//        self::assertDirectoryExists($config['tests-dir'] . '/Unit');
//        self::assertFileExists($config['tests-dir'] . '/AbstractTestCase.php');
//    }
//
//    public function testCreateWithConsoleProjectType(): void
//    {
//        $config = $this->arrangeConfig();
//
//        GenerateFolderStructureAndFiles::create($config, DiscoveryContract::CONSOLE_PROJECT, new NullIO());
//
//        $this->arrangeAssertDirectoryExists($config, ['resources-dir', 'public-dir']);
//
//        self::assertDirectoryExists($config['app-dir'] . '/Console');
//        self::assertDirectoryExists($config['app-dir'] . '/Provider');
//        self::assertDirectoryNotExists($config['app-dir'] . '/Http/Middleware');
//        self::assertFileNotExists($config['app-dir'] . '/Http/Controller/Controller.php');
//
//        self::assertFileNotExists($config['routes-dir'] . '/api.php');
//        self::assertFileExists($config['routes-dir'] . '/console.php');
//        self::assertFileNotExists($config['routes-dir'] . '/web.php');
//
//        self::assertDirectoryNotExists($this->path . '/resources/lang');
//        self::assertDirectoryNotExists($this->path . '/resources/views');
//
//        self::assertFileExists($config['storage-dir'] . '/framework/.gitignore');
//        self::assertFileExists($config['storage-dir'] . '/logs/.gitignore');
//
//        self::assertDirectoryNotExists($config['tests-dir'] . '/Feature');
//        self::assertDirectoryExists($config['tests-dir'] . '/Unit');
//        self::assertFileExists($config['tests-dir'] . '/AbstractTestCase.php');
//    }
//
//    public function testCreateWithHttpProjectType(): void
//    {
//        $config = $this->arrangeConfig();
//
//        GenerateFolderStructureAndFiles::create($config, DiscoveryContract::HTTP_PROJECT, new NullIO());
//
//        $this->arrangeAssertDirectoryExists($config);
//
//        self::assertDirectoryNotExists($config['app-dir'] . '/Console');
//        self::assertDirectoryExists($config['app-dir'] . '/Provider');
//        self::assertDirectoryExists($config['app-dir'] . '/Http/Middleware');
//        self::assertFileExists($config['app-dir'] . '/Http/Controller/Controller.php');
//
//        self::assertFileExists($config['routes-dir'] . '/api.php');
//        self::assertFileNotExists($config['routes-dir'] . '/console.php');
//        self::assertFileExists($config['routes-dir'] . '/web.php');
//
//        self::assertDirectoryExists($config['resources-dir'] . '/lang');
//        self::assertDirectoryExists($config['resources-dir'] . '/views');
//
//        self::assertFileExists($config['storage-dir'] . '/framework/.gitignore');
//        self::assertFileExists($config['storage-dir'] . '/logs/.gitignore');
//
//        self::assertDirectoryExists($config['tests-dir'] . '/Feature');
//        self::assertDirectoryExists($config['tests-dir'] . '/Unit');
//        self::assertFileExists($config['tests-dir'] . '/AbstractTestCase.php');
//    }
//
//    /**
//     * @return array
//     */
//    protected function arrangeConfig(): array
//    {
//        return [
//            'app-dir'        => $this->path . '/app',
//            'public-dir'     => $this->path . '/public',
//            'resources-dir'  => $this->path . '/resources',
//            'routes-dir'     => $this->path . '/routes',
//            'tests-dir'      => $this->path . '/tests',
//            'storage-dir'    => $this->path . '/storage',
//            'discovery_test' => true,
//        ];
//    }
//
//    /**
//     * @param array $config
//     * @param array $skip
//     */
//    protected function arrangeAssertDirectoryExists(array $config, array $skip = []): void
//    {
//        foreach ($config as $key => $dir) {
//            if (\in_array($key, \array_merge(['discovery_test'], $skip), true)) {
//                continue;
//            }
//
//            self::assertDirectoryExists($dir);
//        }
//    }
//}
