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
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->webServerConfig = new WebServerConfig(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', 'local', $this->arrangeAbstractCommandOptions());
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
    }

    public function testGetPort(): void
    {
        static::assertSame('80', $this->webServerConfig->getPort());
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

    /**
     * @param false|string $router
     *
     * @return \Mockery\MockInterface|\Viserio\Component\Console\Command\AbstractCommand
     */
    private function arrangeAbstractCommandOptions($router = false)
    {
        $commandMock = $this->mock(AbstractCommand::class);
        $commandMock->shouldReceive('hasOption')
            ->once()
            ->with('host')
            ->andReturn(true);
        $commandMock->shouldReceive('option')
            ->once()
            ->with('host')
            ->andReturn('127.0.0.1');
        $commandMock->shouldReceive('hasOption')
            ->once()
            ->with('port')
            ->andReturn(true);
        $commandMock->shouldReceive('option')
            ->once()
            ->with('port')
            ->andReturn('80');
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
            ->andReturn(false);
        $commandMock->shouldReceive('hasOption')
            ->once()
            ->with('disable-xdebug')
            ->andReturn(false);

        return $commandMock;
    }
}
