<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\WebServer\Tests;

use Mockery;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Viserio\Component\Console\Command\AbstractCommand;
use Viserio\Component\WebServer\WebServerConfig;
use Viserio\Contract\Config\Exception\InvalidArgumentException as ConfigInvalidArgumentException;
use Viserio\Contract\WebServer\Exception\InvalidArgumentException;

/**
 * @internal
 *
 * @small
 */
final class WebServerConfigTest extends MockeryTestCase
{
    /** @var \Viserio\Component\WebServer\WebServerConfig */
    private $webServerConfig;

    /** @var string */
    private $fixturePath;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturePath = __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture';
        $this->webServerConfig = new WebServerConfig($this->fixturePath, 'local', $this->arrangeAbstractCommandOptions());
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        unset($_ENV['APP_FRONT_CONTROLLER']);
    }

    public function testGetDocumentRoot(): void
    {
        self::assertSame(__DIR__ . \DIRECTORY_SEPARATOR . 'Fixture', $this->webServerConfig->getDocumentRoot());
    }

    public function testGetEnv(): void
    {
        self::assertSame('local', $this->webServerConfig->getEnv());
    }

    public function testGetRouter(): void
    {
        self::assertSame(
            \dirname(__DIR__, 1) . \DIRECTORY_SEPARATOR . 'Resources' . \DIRECTORY_SEPARATOR . 'router.php',
            $this->webServerConfig->getRouter()
        );
    }

    public function testGetHostname(): void
    {
        self::assertSame('127.0.0.1', $this->webServerConfig->getHostname());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, null));

        self::assertStringContainsString('127.0.0.1', $webServerConfig->getHostname());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, '*'));

        self::assertStringContainsString('0.0.0.0', $webServerConfig->getHostname());
    }

    public function testGetPort(): void
    {
        self::assertSame('80', $this->webServerConfig->getPort());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, null));

        self::assertSame('80', $webServerConfig->getPort());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, '127.0.0.1', null));

        self::assertSame('8000', $webServerConfig->getPort());
    }

    public function testGetAddress(): void
    {
        self::assertSame('127.0.0.1:80', $this->webServerConfig->getAddress());
    }

    public function testThrowExceptionOnNotFoundController(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to find the front controller under [' . __DIR__ . '] (none of these files exist: [index_local.php, index.php]).');

        new WebServerConfig(__DIR__, 'local', $this->arrangeAbstractCommandOptions());
    }

    public function testConfigDocumentRootValidatorThrowsExceptionOnWrongDir(): void
    {
        $this->expectException(ConfigInvalidArgumentException::class);
        $this->expectExceptionMessage('The document root directory [test] does not exist.');

        new WebServerConfig('test', '', $this->arrangeAbstractCommandOptions());
    }

    public function testConfigRouterValidatorThrowsExceptionOnWrongType(): void
    {
        $this->expectException(ConfigInvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [router]; Expected [string], but got [NULL], in [Viserio\Component\WebServer\WebServerConfig].');

        new WebServerConfig(__DIR__, 'dev', $this->arrangeAbstractCommandOptions(null));
    }

    public function testConfigRouterValidatorThrowsExceptionOnWrongFile(): void
    {
        $this->expectException(ConfigInvalidArgumentException::class);
        $this->expectExceptionMessage('Router script [test] does not exist.');

        new WebServerConfig(__DIR__, 'dev', $this->arrangeAbstractCommandOptions('test'));
    }

    public function testHasXdebug(): void
    {
        self::assertFalse($this->webServerConfig->hasXdebug());
    }

    public function testGetDisplayAddress(): void
    {
        self::assertNull($this->webServerConfig->getDisplayAddress());

        $webServerConfig = new WebServerConfig($this->fixturePath, 'dev', $this->arrangeAbstractCommandOptions(false, '0.0.0.0'));

        self::assertStringContainsString(':80', $webServerConfig->getDisplayAddress());
    }

    public function testGetPidFile(): void
    {
        self::assertNull($this->webServerConfig->getPidFile());

        $webServerConfig = new WebServerConfig(
            $this->fixturePath,
            'dev',
            $this->arrangeAbstractCommandOptions(false, '0.0.0.0', 80, 'test.pid')
        );

        self::assertStringContainsString('test.pid', $webServerConfig->getPidFile());
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
        $router = false,
        $host = '127.0.0.1',
        $port = 80,
        ?string $pidfile = null
    ) {
        $commandMock = Mockery::mock(AbstractCommand::class);
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
