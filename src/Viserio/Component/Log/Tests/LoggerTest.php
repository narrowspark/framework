<?php
declare(strict_types=1);
namespace Viserio\Component\Log\Tests;

use LogicException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\TestHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\PsrLogMessageProcessor;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Viserio\Component\Events\EventManager;
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
     * @var \Monolog\Handler\TestHandler
     */
    private $handler;

    /**
     * @var \Viserio\Component\Log\Logger
     */
    private $psr3Logger;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockedLogger = $this->mock(MonologLogger::class);
        $this->logger       = new Logger($this->mockedLogger);

        /* @var MonologLogger $psr3Logger*/
        $psr3Logger    = new Logger(new MonologLogger('test'));
        $psr3Logger->pushHandler($handler = new TestHandler());
        $psr3Logger->pushProcessor(new PsrLogMessageProcessor());

        $handler->setFormatter(new LineFormatter('%level_name% %message%'));

        $this->handler   = $handler;
        $this->psr3Logger = $psr3Logger;
    }

    public function testGetMonolog(): void
    {
        $this->logger = new Logger(new MonologLogger('name'));

        self::assertInstanceOf(LoggerInterface::class, $this->logger->getMonolog());
    }

    public function testCallToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('getName')
            ->once();

        $this->logger->getName();
    }

    public function testMethodsPassErrorAdditionsToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('error')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager(new EventManager());
        $this->logger->error('foo');
    }

    public function testMethodsPassEmergencyAdditionsToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('emergency')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager(new EventManager());
        $this->logger->emergency('foo');
    }

    public function testMethodsPassAlertAdditionsToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('alert')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager(new EventManager());
        $this->logger->alert('foo');
    }

    public function testMethodsPassCriticalAdditionsToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('critical')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager(new EventManager());
        $this->logger->critical('foo');
    }

    public function testMethodsPassWarningAdditionsToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('warning')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager(new EventManager());
        $this->logger->warning('foo');
    }

    public function testMethodsPassNoticeAdditionsToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('notice')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager(new EventManager());
        $this->logger->notice('foo');
    }

    public function testMethodsPassInfoAdditionsToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('info')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager(new EventManager());
        $this->logger->info('foo');
    }

    public function testMethodsPassDebugAdditionsToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('debug')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager(new EventManager());
        $this->logger->debug('foo');
    }

    public function testMethodsPassDebugWithLogAdditionsToMonolog(): void
    {
        $this->mockedLogger->shouldReceive('debug')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager(new EventManager());
        $this->logger->log('debug', 'foo');
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
        $this->mockedLogger->shouldReceive('error')
            ->once()
            ->with('foo', []);

        $this->logger->setEventManager($events);
        $this->logger->error('foo');

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
        $this->mockedLogger->shouldReceive('info')
            ->once();
        $this->mockedLogger->shouldReceive('warning')
            ->once()
            ->with(\json_encode(['message' => true], JSON_PRETTY_PRINT), []);
        $this->mockedLogger->shouldReceive('debug')
            ->once()
            ->with(\var_export((new ArrayableClass())->toArray(), true), []);

        $this->logger->log('info', ['message' => true]);
        $this->logger->log('debug', new ArrayableClass());
        $this->logger->log('warning', new JsonableClass());
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
        $this->psr3Logger->{$level}($message, ['user' => 'Bob']);
        $this->psr3Logger->log($level, $message, ['user' => 'Bob']);

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
        $this->psr3Logger->log('invalid level', 'Foo');
    }

    public function testContextReplacement(): void
    {
        $this->psr3Logger->info('{Message {nothing} {user} {foo.bar} a}', ['user' => 'Bob', 'foo.bar' => 'Bar']);

        self::assertEquals(['info {Message {nothing} Bob Bar a}'], $this->getLogs());
    }

    public function testObjectCastToString(): void
    {
        $dummy = $this->mock(DummyToString::class);

        $dummy->shouldReceive('__toString')
            ->once()
            ->andReturn('DUMMY');

        $this->psr3Logger->warning($dummy);

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
            'resource' => fopen('php://memory', 'rb'),
        ];

        $this->psr3Logger->warning('Crazy context data', $context);

        self::assertEquals(['warning Crazy context data'], $this->getLogs());
    }

    public function testContextExceptionKeyCanBeExceptionOrOtherValues(): void
    {
        $this->psr3Logger->warning('Random message', ['exception' => 'oops']);
        $this->psr3Logger->critical('Uncaught Exception!', ['exception' => new LogicException('Fail')]);

        $expected = [
            'warning Random message',
            'critical Uncaught Exception!',
        ];

        self::assertEquals($expected, $this->getLogs());
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
    private function getLogs()
    {
        $convert = function ($record) {
            $lower = function ($match) {
                return mb_strtolower($match[0]);
            };

            return preg_replace_callback('{^[A-Z]+}', $lower, $record['formatted']);
        };

        return array_map($convert, $this->handler->getRecords());
    }
}
