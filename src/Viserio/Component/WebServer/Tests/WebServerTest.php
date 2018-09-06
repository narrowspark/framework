<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests;

use PHPUnit\Framework\TestCase;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverInvalidArgumentException;
use Viserio\Component\Contract\WebServer\Exception\InvalidArgumentException;
use Viserio\Component\Contract\WebServer\Exception\RuntimeException;
use Viserio\Component\WebServer\WebServer;

/**
 * @internal
 */
final class WebServerTest extends TestCase
{
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

        $this->path = __DIR__ . \DIRECTORY_SEPARATOR . '.web-server-pid';

        @\file_put_contents($this->path, '127.0.0.1:8080');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        StaticMemory::$result = false;

        @\unlink($this->path);
    }

    public function testGetDefaultOptions(): void
    {
        static::assertSame(
            [
                'router'  => \dirname(__DIR__) . \DIRECTORY_SEPARATOR . 'Resources' . \DIRECTORY_SEPARATOR . 'router.php',
                'host'    => null,
                'port'    => null,
            ],
            WebServer::getDefaultOptions()
        );
    }

    public function testStopToThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No web server is listening.');

        WebServer::stop('');
    }

    public function testStop(): void
    {
        WebServer::stop($this->path);

        static::assertFileNotExists($this->path);
    }

    public function testGetAddress(): void
    {
        static::assertFalse(WebServer::getAddress(''));
        static::assertSame('127.0.0.1:8080', WebServer::getAddress($this->path));
    }

    public function testIsRunning(): void
    {
        static::assertFalse(WebServer::isRunning(''));

        StaticMemory::$result = \fopen('php://temp', 'r+b');

        static::assertTrue(WebServer::isRunning($this->path));
    }

    public function testConfigDocumentRootValidatorThrowsExceptionOnWrongType(): void
    {
        $this->expectException(OptionsResolverInvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [document_root]; Expected [string], but got [NULL], in [Viserio\Component\WebServer\WebServer].');

        WebServer::start(['document_root' => null, 'env' => '']);
    }

    public function testConfigDocumentRootValidatorThrowsExceptionOnWrongDir(): void
    {
        $this->expectException(OptionsResolverInvalidArgumentException::class);
        $this->expectExceptionMessage('The document root directory [test] does not exist.');

        WebServer::start(['document_root' => 'test', 'env' => '']);
    }

    public function testConfigRouterValidatorThrowsExceptionOnWrongType(): void
    {
        $this->expectException(OptionsResolverInvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [router]; Expected [string], but got [NULL], in [Viserio\Component\WebServer\WebServer].');

        WebServer::start(['document_root' => __DIR__, 'env' => 'dev', 'router' => null]);
    }

    public function testConfigRouterValidatorThrowsExceptionOnWrongFile(): void
    {
        $this->expectException(OptionsResolverInvalidArgumentException::class);
        $this->expectExceptionMessage('Router script [test] does not exist.');

        WebServer::start(['document_root' => __DIR__, 'env' => 'dev', 'router' => 'test']);
    }

    public function testRunToThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A process is already listening on http://127.0.0.1:8000.');

        $path = \getcwd() . \DIRECTORY_SEPARATOR . '.web-server-pid';

        @\file_put_contents($path, '127.0.0.1:8080');

        StaticMemory::$result = \fopen('php://temp', 'r+b');

        WebServer::run(['document_root' => __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'env' => 'dev']);

        @\unlink($path);
    }

    public function testStartToThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('A process is already listening on http://127.0.0.1:8000.');

        $path = \getcwd() . \DIRECTORY_SEPARATOR . '.web-server-pid';

        @\file_put_contents($path, '127.0.0.1:8080');

        StaticMemory::$result = \fopen('php://temp', 'r+b');

        WebServer::start(['document_root' => __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'env' => 'dev']);

        @\unlink($path);
    }

    public function testStartToThrowExceptionOnUnableStart(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to start the server process.');

        StaticMemory::$result    = false;
        StaticMemory::$pcntlFork = -1;

        WebServer::start(['document_root' => __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'env' => 'dev']);
    }

    public function testStartToReturnStarted(): void
    {
        StaticMemory::$result    = false;
        StaticMemory::$pcntlFork = 1;

        static::assertSame(WebServer::STARTED, WebServer::start(['document_root' => __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'env' => 'dev']));
    }

    public function testStartToThrowExceptionOnChildProcess(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to set the child process as session leader.');

        StaticMemory::$result      = false;
        StaticMemory::$pcntlFork   = 0;
        StaticMemory::$posixSetsid = -1;

        WebServer::start(['document_root' => __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'env' => 'dev']);
    }

    public function testThrowExceptionOnNotFoundController(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to find the front controller under [' . __DIR__ . '] (none of these files exist: [index_dev.php, index.php]).');

        WebServer::start(['document_root' => __DIR__, 'env' => 'dev']);
    }
}
