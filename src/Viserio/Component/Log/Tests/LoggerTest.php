<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests;

use Monolog\Logger as MonologLogger;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Viserio\Component\Events\EventManager;
use Viserio\Component\Log\HandlerParser;
use Viserio\Component\Log\Logger;
use Viserio\Component\Log\Tests\Fixture\ArrayableClass;
use Viserio\Component\Log\Tests\Fixture\DummyToString;
use Viserio\Component\Log\Tests\Fixture\JsonableClass;

class LoggerTest extends MockeryTestCase
{
    /**
     * @var \Mockery\MockInterface|\Psr\Log\LoggerInterface
     */
    private $mockedLogger;

    /**
     * @var \Viserio\Component\Log\Logger
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedLogger = $this->mock(MonologLogger::class);
        $this->logger       = new Logger($this->mockedLogger);
    }

    public function testGetMonolog(): void
    {
        $writer = new Logger(new MonologLogger('name'));

        self::assertInstanceOf(LoggerInterface::class, $writer->getMonolog());
    }

    public function testCallToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('pushProcessor')
            ->once();
        $this->mockedLogger->shouldReceive('getName')
            ->once();

        $this->logger->getName();
    }

    public function testMethodsPassErrorAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('error')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->error('foo');
    }

    public function testMethodsPassEmergencyAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('emergency')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->emergency('foo');
    }

    public function testMethodsPassAlertAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('alert')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->alert('foo');
    }

    public function testMethodsPassCriticalAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('critical')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->critical('foo');
    }

    public function testMethodsPassWarningAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('warning')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->warning('foo');
    }

    public function testMethodsPassNoticeAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('notice')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->notice('foo');
    }

    public function testMethodsPassInfoAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('info')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->info('foo');
    }

    public function testMethodsPassDebugAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('debug')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->debug('foo');
    }

    public function testMethodsPassDebugWithLogAdditionsToMonolog(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('debug')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager(new EventManager());
        $writer->log('debug', 'foo');
    }

    public function testWriterTriggerEventManager(): void
    {
        $events = new EventManager();
        $events->attach(
            Logger::MESSAGE,
            function ($event): void {
                $_SERVER['__log.level'] = $event->getLevel();
                $_SERVER['__log.message'] = $event->getMessage();
                $_SERVER['__log.context'] = $event->getContext();
            }
        );
        $monolog = $this->mock(Logger::class);
        $monolog
            ->shouldReceive('error')
            ->once()
            ->with('foo', []);
        $monolog
            ->shouldReceive('pushProcessor')
            ->once();

        $writer = new Logger(new HandlerParser($monolog));
        $writer->setEventManager($events);
        $writer->error('foo');

        self::assertTrue(isset($_SERVER['__log.level']));
        self::assertEquals('error', $_SERVER['__log.level']);

        unset($_SERVER['__log.level']);

        self::assertTrue(isset($_SERVER['__log.message']));
        self::assertEquals('foo', $_SERVER['__log.message']);

        unset($_SERVER['__log.message']);

        self::assertTrue(isset($_SERVER['__log.context']));
        self::assertEquals([], $_SERVER['__log.context']);

        unset($_SERVER['__log.context']);
    }

    public function testMessageInput(): void
    {
        $monolog = $this->mock(Logger::class);
        $monolog->shouldReceive('pushProcessor')
            ->once();
        $monolog->shouldReceive('info')
            ->once();
        $monolog->shouldReceive('warning')
            ->once()
            ->with(\json_encode(['message' => true], JSON_PRETTY_PRINT), []);
        $monolog->shouldReceive('debug')
            ->once()
            ->with(\var_export((new ArrayableClass())->toArray(), true), []);

        $writer = new Logger(new HandlerParser($monolog));
        $writer->log('info', ['message' => true]);
        $writer->log('debug', new ArrayableClass());
        $writer->log('warning', new JsonableClass());
    }

    /**
     * This must return the log messages in order.
     *
     * The simple formatting of the messages is: "<LOG LEVEL> <MESSAGE>".
     *
     * Example ->error('Foo') would yield "error Foo".
     *
     * @return string[]
     */
    public function getLogs()
    {
        return [];
    }

    public function testImplements(): void
    {
        self::assertInstanceOf(LoggerInterface::class, $this->logger);
    }

    /**
     * @dataProvider provideLevelsAndMessages
     *
     * @param mixed $level
     * @param mixed $message
     */
    public function testLogsAtAllLevels($level, $message): void
    {
        $logger = $this->logger;
        $logger->{$level}($message, ['user' => 'Bob']);
        $logger->log($level, $message, ['user' => 'Bob']);

        $expected = [
            $level . ' message of level ' . $level . ' with context: Bob',
            $level . ' message of level ' . $level . ' with context: Bob',
        ];
        self::assertEquals($expected, $this->getLogs());
    }

    public function provideLevelsAndMessages()
    {
        return [
            LogLevel::EMERGENCY => [LogLevel::EMERGENCY, 'message of level emergency with context: {user}'],
            LogLevel::ALERT     => [LogLevel::ALERT, 'message of level alert with context: {user}'],
            LogLevel::CRITICAL  => [LogLevel::CRITICAL, 'message of level critical with context: {user}'],
            LogLevel::ERROR     => [LogLevel::ERROR, 'message of level error with context: {user}'],
            LogLevel::WARNING   => [LogLevel::WARNING, 'message of level warning with context: {user}'],
            LogLevel::NOTICE    => [LogLevel::NOTICE, 'message of level notice with context: {user}'],
            LogLevel::INFO      => [LogLevel::INFO, 'message of level info with context: {user}'],
            LogLevel::DEBUG     => [LogLevel::DEBUG, 'message of level debug with context: {user}'],
        ];
    }

    /**
     * @expectedException \Psr\Log\InvalidArgumentException
     */
    public function testThrowsOnInvalidLevel(): void
    {
        $logger = $this->logger;
        $logger->log('invalid level', 'Foo');
    }

    public function testContextReplacement(): void
    {
        $logger = $this->logger;
        $logger->info('{Message {nothing} {user} {foo.bar} a}', ['user' => 'Bob', 'foo.bar' => 'Bar']);

        $expected = ['info {Message {nothing} Bob Bar a}'];
        self::assertEquals($expected, $this->getLogs());
    }

    public function testObjectCastToString(): void
    {
        $dummy = $this->mock(DummyToString::class, ['__toString']);

        $dummy->shouldReceive('__toString')
            ->once()
            ->andReturn('DUMMY');

        $this->logger->warning($dummy);

        self::assertEquals(['warning DUMMY'], $this->getLogs());
    }

    public function testContextCanContainAnything(): void
    {
        $context = [
            'bool'     => true,
            'null'     => null,
            'string'   => 'Foo',
            'int'      => 0,
            'float'    => 0.5,
            'nested'   => ['with object' => new DummyToString()],
            'object'   => new \DateTime(),
            'resource' => fopen('php://memory', 'r'),
        ];

        $this->logger->warning('Crazy context data', $context);

        self::assertEquals(['warning Crazy context data'], $this->getLogs());
    }

    public function testContextExceptionKeyCanBeExceptionOrOtherValues(): void
    {
        $logger = $this->logger;
        $logger->warning('Random message', ['exception' => 'oops']);
        $logger->critical('Uncaught Exception!', ['exception' => new \LogicException('Fail')]);

        $expected = [
            'warning Random message',
            'critical Uncaught Exception!',
        ];

        self::assertEquals($expected, $this->getLogs());
    }
}
