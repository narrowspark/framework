<?php
declare(strict_types=1);
namespace Viserio\Bridge\Monolog\Tests\Handler;

use DateTime;
use Monolog\Logger;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Viserio\Bridge\Monolog\Formatter\ConsoleFormatter;
use Viserio\Bridge\Monolog\Handler\ConsoleHandler;
use Viserio\Component\Console\ConsoleEvents;
use Viserio\Component\Console\Event\ConsoleCommandEvent;
use Viserio\Component\Console\Event\ConsoleTerminateEvent;
use Viserio\Component\Events\EventManager;

/**
 * Tests the ConsoleHandler and also the ConsoleFormatter.
 *
 * @author Tobias Schultze <http://tobion.de>
 * @copyright Copyright (c) 2004-2017 Fabien Potencier
 *
 * @internal
 */
final class ConsoleHandlerTest extends MockeryTestCase
{
    public function testConstructor(): void
    {
        $handler = new ConsoleHandler(null, false);

        $this->assertFalse($handler->getBubble(), 'the bubble parameter gets propagated');
    }

    public function testIsHandling(): void
    {
        $handler = new ConsoleHandler();

        $this->assertFalse($handler->isHandling([]), '->isHandling returns false when no output is set');
    }

    /**
     * @dataProvider provideVerbosityMappingTests
     *
     * @param int   $verbosity
     * @param int   $level
     * @param bool  $isHandling
     * @param array $map
     */
    public function testVerbosityMapping(int $verbosity, int $level, bool $isHandling, array $map = []): void
    {
        /** @var \Mockery\MockInterface|\Symfony\Component\Console\Output\OutputInterface $outputMock */
        $outputMock = $this->mock(OutputInterface::class);
        $outputMock->shouldReceive('getVerbosity')
            ->andReturn($verbosity);

        $handler = new ConsoleHandler($outputMock, true, $map);

        $this->assertSame(
            $isHandling,
            $handler->isHandling(['level' => $level]),
            '->isHandling returns correct value depending on console verbosity and log level'
        );

        // check that the handler actually outputs the record if it handles it
        $levelName = Logger::getLevelName($level);
        $levelName = \sprintf('%-9s', $levelName);

        /** @var \Mockery\MockInterface|\Symfony\Component\Console\Output\Output $realOutputMock */
        $realOutputMock = $this->mock(Output::class . '[doWrite]')
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $realOutputMock->setVerbosity($verbosity);

        $log = "16:21:54 ${levelName} [app] My info message [] []\n";

        if ($realOutputMock->isDebug()) {
            $log = "16:21:54 ${levelName} [app] My info message\n[]\n[]\n";
        }

        $realOutputMock->shouldReceive('doWrite')
            ->times($isHandling ? 1 : 0)
            ->with($log, false);
        $handler = new ConsoleHandler($realOutputMock, true, $map);

        $infoRecord = [
            'message'    => 'My info message',
            'context'    => [],
            'level'      => $level,
            'level_name' => Logger::getLevelName($level),
            'channel'    => 'app',
            'datetime'   => new DateTime('2013-05-29 16:21:54'),
            'extra'      => [],
        ];

        $this->assertFalse($handler->handle($infoRecord), 'The handler finished handling the log.');
    }

    /**
     * @return array
     */
    public function provideVerbosityMappingTests(): array
    {
        return [
            [OutputInterface::VERBOSITY_QUIET, Logger::ERROR, true],
            [OutputInterface::VERBOSITY_QUIET, Logger::WARNING, false],
            [OutputInterface::VERBOSITY_NORMAL, Logger::WARNING, true],
            [OutputInterface::VERBOSITY_NORMAL, Logger::NOTICE, false],
            [OutputInterface::VERBOSITY_VERBOSE, Logger::NOTICE, true],
            [OutputInterface::VERBOSITY_VERBOSE, Logger::INFO, false],
            [OutputInterface::VERBOSITY_VERY_VERBOSE, Logger::INFO, true],
            [OutputInterface::VERBOSITY_VERY_VERBOSE, Logger::DEBUG, false],
            [OutputInterface::VERBOSITY_DEBUG, Logger::DEBUG, true],
            [OutputInterface::VERBOSITY_DEBUG, Logger::EMERGENCY, true],
            [OutputInterface::VERBOSITY_NORMAL, Logger::NOTICE, true, [
                OutputInterface::VERBOSITY_NORMAL => Logger::NOTICE,
            ]],
            [OutputInterface::VERBOSITY_DEBUG, Logger::NOTICE, true, [
                OutputInterface::VERBOSITY_NORMAL => Logger::NOTICE,
            ]],
        ];
    }

    public function testVerbosityChanged(): void
    {
        /** @var \Mockery\MockInterface|\Symfony\Component\Console\Output\OutputInterface $outputMock */
        $outputMock = $this->mock(OutputInterface::class);
        $outputMock->shouldReceive('getVerbosity')
            ->ordered(0)
            ->once()
            ->andReturn(OutputInterface::VERBOSITY_QUIET);
        $outputMock->shouldReceive('getVerbosity')
            ->ordered(1)
            ->once()
            ->andReturn(OutputInterface::VERBOSITY_DEBUG);

        $handler = new ConsoleHandler($outputMock);

        $this->assertFalse(
            $handler->isHandling(['level' => Logger::NOTICE]),
            'when verbosity is set to quiet, the handler does not handle the log'
        );
        $this->assertTrue(
            $handler->isHandling(['level' => Logger::NOTICE]),
            'since the verbosity of the output increased externally, the handler is now handling the log'
        );
    }

    public function testGetFormatter(): void
    {
        $handler = new ConsoleHandler();

        $this->assertInstanceOf(
            ConsoleFormatter::class,
            $handler->getFormatter(),
            '-getFormatter returns ConsoleFormatter by default'
        );
    }

    public function testWritingAndFormatting(): void
    {
        /** @var \Mockery\MockInterface|\Symfony\Component\Console\Output\OutputInterface $outputMock */
        $outputMock = $this->mock(OutputInterface::class);
        $outputMock->shouldReceive('getVerbosity')
            ->andReturn(OutputInterface::VERBOSITY_DEBUG);
        $outputMock->shouldReceive('isDecorated')
            ->andReturn(false);
        $outputMock->shouldReceive('write')
            ->once()
            ->andReturn("16:21:54 <fg=green>INFO     </> <comment>[app]</> My info message\n[]\n[]\n");

        $handler = new ConsoleHandler(null, false);
        $handler->setOutput($outputMock);

        $infoRecord = [
            'message'    => 'My info message',
            'context'    => [],
            'level'      => Logger::INFO,
            'level_name' => Logger::getLevelName(Logger::INFO),
            'channel'    => 'app',
            'datetime'   => new DateTime('2013-05-29 16:21:54'),
            'extra'      => [],
        ];

        $this->assertTrue($handler->handle($infoRecord), 'The handler finished handling the log as bubble is false.');
    }

    public function testLogsFromListeners(): void
    {
        $output = new BufferedOutput();
        $output->setVerbosity(OutputInterface::VERBOSITY_DEBUG);

        $handler = new ConsoleHandler(null, false);

        $logger = new Logger('app');
        $logger->pushHandler($handler);

        $dispatcher = new EventManager();
        $dispatcher->attach(ConsoleEvents::COMMAND, static function () use ($logger): void {
            $logger->addInfo('Before command message.');
        });
        $dispatcher->attach(ConsoleEvents::TERMINATE, static function () use ($logger): void {
            $logger->addInfo('Before terminate message.');
        });

        $handler->registerEvents($dispatcher);

        $dispatcher->attach(ConsoleEvents::COMMAND, static function () use ($logger): void {
            $logger->addInfo('After command message.');
        });
        $dispatcher->attach(ConsoleEvents::TERMINATE, static function () use ($logger): void {
            $logger->addInfo('After terminate message.');
        });

        $dispatcher->trigger(new ConsoleCommandEvent(new Command('foo'), $this->mock(InputInterface::class), $output));

        $this->assertContains('Before command message.', $out = $output->fetch());
        $this->assertContains('After command message.', $out);

        $dispatcher->trigger(new ConsoleTerminateEvent(new Command('foo'), $this->mock(InputInterface::class), $output, 0));

        $this->assertContains('Before terminate message.', $out = $output->fetch());
        $this->assertContains('After terminate message.', $out);
    }

    /**
     * {@inheritdoc}
     */
    protected function allowMockingNonExistentMethods(bool $allow = false): void
    {
        parent::allowMockingNonExistentMethods(true);
    }
}
