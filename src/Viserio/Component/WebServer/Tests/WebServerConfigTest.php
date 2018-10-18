<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests;

use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException as OptionsResolverInvalidArgumentException;
use Viserio\Component\Contract\WebServer\Exception\InvalidArgumentException;
use Viserio\Component\WebServer\WebServerConfig;

/**
 * @internal
 */
final class WebServerConfigTest extends MockeryTestCase
{
    /**
     * @var \Viserio\Component\WebServer\WebServerConfig
     */
    private $webServerConfig;

    /**
     * @var string
     */
    private $fixturePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturePath     =__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture';
        $this->webServerConfig = new WebServerConfig($this->fixturePath, 'local', $this->arrangeAbstractCommandOptions());
    }

    public function testGetDocumentRoot(): void
    {
        static::assertSame(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', $this->webServerConfig->getDocumentRoot());
    }

    public function testGetEnv(): void
    {
        static::assertSame('local', $this->webServerConfig->getEnv());
    }

    public function testGetRouter(): void
    {
        static::assertSame(
            \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Resources' . \DIRECTORY_SEPARATOR . 'router.php',
            $this->webServerConfig->getRouter()
        );
    }

    public function testGetHostname(): void
    {
        static::assertSame('127.0.0.1', $this->webServerConfig->getHostname());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, null));

        static::assertContains('127.0.0.1', $webServerConfig->getHostname());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, '*'));

        static::assertContains('0.0.0.0', $webServerConfig->getHostname());
    }

    public function testGetPort(): void
    {
        static::assertSame('80', $this->webServerConfig->getPort());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, null));

        static::assertSame('8000', $webServerConfig->getPort());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, '127.0.0.1', null));

        static::assertSame('8000', $webServerConfig->getPort());
    }

    public function testGetAddress(): void
    {
        static::assertSame('127.0.0.1:80', $this->webServerConfig->getAddress());
    }

    public function testThrowExceptionOnNotFoundController(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to find the front controller under [' . __DIR__ . '] (none of these files exist: [index_local.php, index.php]).');

        new WebServerConfig(__DIR__, 'local', $this->arrangeAbstractCommandOptions());
    }

    public function testConfigDocumentRootValidatorThrowsExceptionOnWrongDir(): void
    {
        $this->expectException(OptionsResolverInvalidArgumentException::class);
        $this->expectExceptionMessage('The document root directory [test] does not exist.');

        new WebServerConfig('test', '', $this->arrangeAbstractCommandOptions());
    }

    public function testConfigRouterValidatorThrowsExceptionOnWrongType(): void
    {
        $this->expectException(OptionsResolverInvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [router]; Expected [string], but got [NULL], in [Viserio\Component\WebServer\WebServerConfig].');

        new WebServerConfig(__DIR__, 'dev', $this->arrangeAbstractCommandOptions(null));
    }

    public function testConfigRouterValidatorThrowsExceptionOnWrongFile(): void
    {
        $this->expectException(OptionsResolverInvalidArgumentException::class);
        $this->expectExceptionMessage('Router script [test] does not exist.');

        new WebServerConfig(__DIR__, 'dev', $this->arrangeAbstractCommandOptions('test'));
    }

    public function testHasXdebug(): void
    {
        static::assertFalse($this->webServerConfig->hasXdebug());
    }

    public function testGetDisplayAddress(): void
    {
        static::assertNull($this->webServerConfig->getDisplayAddress());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, '0.0.0.0'));

        static::assertContains(':80', $webServerConfig->getDisplayAddress());
    }

    public function testGetPidFile(): void
    {
        static::assertNull($this->webServerConfig->getPidFile());

        $webServerConfig = new WebServerConfig(
            $this->fixturePath,
            'dev',
            $this->arrangeAbstractCommandOptions(false, '0.0.0.0', 80, 'test.pid')
        );

        static::assertContains('test.pid', $webServerConfig->getPidFile());
    }

    /**
     * @param false|string    $router
     * @param null|string     $host
     * @param null|int|string $port
     * @param null|string     $pidfile
     *
     * @return \Mockery\MockInterface|\Viserio\Component\Console\Command\AbstractCommand
     */
    private function arrangeAbstractCommandOptions(
        $router          = false,
        $host            = '127.0.0.1',
        $port            = 80,
        ?string $pidfile = null
    ) {
        $commandMock = $this->mock(AbstractCommand::class);
        $commandMock->shouldReceive('hasOption')
            ->once()
            ->with('host')
            ->andReturn(true);
        $commandMock->shouldReceive('option')
            ->once()
            ->with('host')
            ->andReturn($host);
        $commandMock->shouldReceive('hasOption')
            ->once()
            ->with('port')
            ->andReturn(true);
        $commandMock->shouldReceive('option')
            ->once()
            ->with('port')
            ->andReturn($port);
        $commandMock->shouldReceive('hasOption')
            ->once()
            ->with('router')
            ->andReturn(true);
        $commandMock->shouldReceive('option')
            ->once()
            ->with('router')
            ->andReturn($router !== false ? $router : \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Resources' . \DIRECTORY_SEPARATOR . 'router.php');
        $commandMock->shouldReceive('hasOption')
            ->once()
            ->with('pidfile')
            ->andReturn($pidfile !== null);

        if ($pidfile !== null) {
            $commandMock->shouldReceive('option')
                ->once()
                ->with('pidfile')
                ->andReturn($pidfile);
        }

        $commandMock->shouldReceive('hasOption')
            ->once()
            ->with('disable-xdebug')
            ->andReturn(false);

        return $commandMock;
    }
}
