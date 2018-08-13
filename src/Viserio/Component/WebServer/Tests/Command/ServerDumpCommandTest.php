<?php
declare(strict_types=1);
namespace Viserio\Component\WebServer\Tests;

use Symfony\Component\VarDumper\Server\DumpServer;
use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\Contract\WebServer\Exception\RuntimeException;
use Viserio\Component\WebServer\Command\ServerDumpCommand;

/**
 * @internal
 */
final class ServerDumpCommandTest extends CommandTestCase
{
    /**
     * @var \Mockery\MockInterface|\Viserio\Component\WebServer\Command\ServerDumpCommand
     */
    private $serverMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverMock = \Mockery::mock(DumpServer::class);
    }

    public function testCommand(): void
    {
        $this->serverMock->shouldReceive('start')
            ->once();
        $this->serverMock->shouldReceive('getHost')
            ->once()
            ->andReturn('http://127.0.0.1:8080');
        $this->serverMock->shouldReceive('listen')
            ->once();

        $output = $this->executeCommand(new ServerDumpCommand($this->serverMock));

        static::assertSame("Symfony Var Dumper Server\n\n [OK] Server listening on http://127.0.0.1:8080                                 \n\nQuit the server with CONTROL-C.", \trim($output->getDisplay(true)));
        static::assertSame(0, $output->getStatusCode());
    }

    public function testCommandThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported format [css].');

        $this->executeCommand(new ServerDumpCommand($this->serverMock), ['--format' => 'css']);
    }
}
