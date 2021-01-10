<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\WebServer\Tests\Command;

use Mockery;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\Server\DumpServer;
use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\WebServer\Command\ServerDumpCommand;
use Viserio\Contract\WebServer\Exception\RuntimeException;

/**
 * @internal
 *
 * @small
 * @coversNothing
 */
final class ServerDumpCommandTest extends CommandTestCase
{
    /** @var \Mockery\MockInterface|\Symfony\Component\VarDumper\Server\DumpServer */
    private $serverMock;

    /** @var \Symfony\Component\VarDumper\Dumper\CliDumper */
    private $cliDumper;

    /** @var \Symfony\Component\VarDumper\Dumper\HtmlDumper */
    private $htmlDumper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverMock = Mockery::mock(DumpServer::class);
        $this->cliDumper = new CliDumper();
        $this->htmlDumper = new HtmlDumper();
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

        $output = $this->executeCommand(new ServerDumpCommand($this->serverMock, $this->cliDumper, $this->htmlDumper));

        $messages = \trim($output->getDisplay(true));

        self::assertStringContainsString('Symfony Var Dumper Server', $messages);
        self::assertStringContainsString('[OK] Server listening on', $messages);
        self::assertStringContainsString('http://127.0.0.1:8080', $messages);
        self::assertStringContainsString('Quit the server with CONTROL-C.', $messages);

        self::assertSame(0, $output->getStatusCode());
    }

    public function testCommandThrowException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unsupported format [css].');

        $this->executeCommand(new ServerDumpCommand($this->serverMock, $this->cliDumper, $this->htmlDumper), ['--format' => 'css']);
    }
}
