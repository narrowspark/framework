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

namespace Viserio\Component\Log;

use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SlackWebhookHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Viserio\Component\Log\Traits\ParseLevelTrait;
use Viserio\Component\Manager\AbstractManager;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Contract\Log\Exception\InvalidArgumentException;
use Viserio\Contract\Log\Exception\RuntimeException;
use Viserio\Contract\Manager\Exception\InvalidArgumentException as ManagerInvalidArgumentException;

class LogManager extends AbstractManager implements LoggerInterface,
    ProvidesDefaultConfigContract
{
    use LoggerTrait;
    use ParseLevelTrait;
    use EventManagerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected const CONFIG_LIST_NAME = 'channels';

    /**
     * Array of monolog processor callbacks.
     *
     * @var callable[]
     */
    private $processors = [];

    /**
     * {@inheritdoc}
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'default' => 'single',
            'name' => 'narrowspark',
            'channels' => [
                'aggregate' => [
                    'driver' => 'aggregate',
                    'channels' => ['single', 'daily'],
                ],
                'single' => [
                    'driver' => 'single',
                    'level' => 'debug',
                ],
                'daily' => [
                    'driver' => 'daily',
                    'level' => 'debug',
                    'days' => 14,
                ],
                'syslog' => [
                    'driver' => 'syslog',
                    'level' => 'debug',
                ],
                'errorlog' => [
                    'driver' => 'errorlog',
                    'level' => 'debug',
                ],
                'slack' => [
                    'driver' => 'slack',
                    'url' => '',
                    'username' => null,
                    'emoji' => ':boom:',
                    'level' => 'critical',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return [
            'path',
            'env',
        ];
    }

    /**
     * Adds a processor on to the stack.
     *
     * @param callable $callback
     *
     * @return $this
     */
    public function pushProcessor(callable $callback): self
    {
        \array_unshift($this->processors, $callback);

        return $this;
    }

    /**
     * Get a log channel instance.
     *
     * @param null|string $channel
     *
     * @return mixed
     */
    public function getChannel(?string $channel = null)
    {
        return $this->getDriver($channel);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $this->getDriver()->{$level}($message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function createDriver(array $config): LoggerInterface
    {
        try {
            $driver = parent::createDriver($config);
        } catch (ManagerInvalidArgumentException $exception) {
            $driver = $this->createEmergencyDriver();
            $driver->emergency(
                'Unable to create configured logger. Using emergency logger.',
                ['exception' => $exception]
            );
        }

        if ($driver instanceof Monolog) {
            $driver = new Logger($this->pushProcessorsToMonolog($config, $driver));
        }

        if ($this->eventManager !== null && \method_exists($driver, 'setEventManager')) {
            $driver->setEventManager($this->eventManager);
        }

        return $driver;
    }

    /**
     * Create a new, on-demand aggregate logger instance.
     *
     * @param array $config
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createStackDriver(array $config): LoggerInterface
    {
        return $this->createAggregateDriver(['channels' => $config['channels'], 'channel' => $config['channel'] ?? null]);
    }

    /**
     * Create a aggregate log driver instance.
     *
     * @param array $config
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createAggregateDriver(array $config): LoggerInterface
    {
        $handlers = [];

        foreach ((array) $config['channels'] as $channel) {
            foreach ($this->getDriver($channel)->getHandlers() as $handler) {
                $handlers[] = $handler;
            }
        }

        return new Monolog($this->parseChannel($config), $handlers);
    }

    /**
     * Create an emergency log handler to avoid white screens of death.
     *
     * @throws Exception
     * @throws \InvalidArgumentException
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createEmergencyDriver(): LoggerInterface
    {
        $handler = new StreamHandler(
            $this->getFilePath(),
            self::parseLevel('debug'),
            $config['bubble'] ?? true,
            $config['permission'] ?? null,
            $config['locking'] ?? false
        );
        $handler->setFormatter($this->getConfiguredLineFormatter());

        return new Monolog($this->resolvedOptions['name'], [$handler]);
    }

    /**
     * Create an instance of the single file log driver.
     *
     * @param array $config
     *
     * @throws Exception
     * @throws \InvalidArgumentException
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createSingleDriver(array $config): LoggerInterface
    {
        $handler = new StreamHandler(
            $this->getFilePath(),
            self::parseLevel($config['level'] ?? 'debug'),
            $config['bubble'] ?? true,
            $config['permission'] ?? null,
            $config['locking'] ?? false
        );
        $handler->setFormatter($this->getConfiguredLineFormatter());

        return new Monolog($this->parseChannel($config), [$handler]);
    }

    /**
     * Create an instance of the daily file log driver.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createDailyDriver(array $config): LoggerInterface
    {
        $handler = new RotatingFileHandler(
            $this->getFilePath(),
            $config['days'] ?? 7,
            self::parseLevel($config['level'] ?? 'debug'),
            $config['bubble'] ?? true,
            $config['permission'] ?? null,
            $config['locking'] ?? false
        );
        $handler->setFormatter($this->getConfiguredLineFormatter());

        return new Monolog($this->parseChannel($config), [$handler]);
    }

    /**
     * Create an instance of the syslog log driver.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createSyslogDriver(array $config): LoggerInterface
    {
        $handler = new SyslogHandler(
            $config['name'],
            $config['facility'] ?? \LOG_USER,
            self::parseLevel($config['level'] ?? 'debug')
        );
        $handler->setFormatter($this->getConfiguredLineFormatter());

        return new Monolog($this->parseChannel($config), [$handler]);
    }

    /**
     * Create an instance of the "error log" log driver.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createErrorlogDriver(array $config): LoggerInterface
    {
        $handler = new ErrorLogHandler(
            $config['type'] ?? ErrorLogHandler::OPERATING_SYSTEM,
            self::parseLevel($config['level'] ?? 'debug')
        );
        $handler->setFormatter($this->getConfiguredLineFormatter());

        return new Monolog($this->parseChannel($config), [$handler]);
    }

    /**
     * Create an instance of the Slack log driver.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createSlackDriver(array $config): LoggerInterface
    {
        $handler = new SlackWebhookHandler(
            $config['url'],
            $config['channel'] ?? null,
            $config['username'] ?? $this->resolvedOptions['name'],
            $config['attachment'] ?? true,
            $config['emoji'] ?? ':boom:',
            $config['short'] ?? false,
            $config['context'] ?? true,
            self::parseLevel($config['level'] ?? 'debug'),
            $config['bubble'] ?? true,
            $config['exclude_fields'] ?? []
        );
        $handler->setFormatter($this->getConfiguredLineFormatter());

        return new Monolog($this->parseChannel($config), [$handler]);
    }

    /**
     * Create a custom log driver instance.
     *
     * @param array $config
     *
     * @throws \Viserio\Contract\Log\Exception\RuntimeException
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createCustomDriver(array $config): LoggerInterface
    {
        $via = $config['via'];
        $config['name'] = $config['original_name'];

        unset($config['original_name']);

        if (\is_callable($via)) {
            return $via($config);
        }

        if ($this->container !== null && $this->container->has($via)) {
            return $this->container->get($via);
        }

        throw new RuntimeException(\sprintf('Given custom logger [%s] could not be resolved.', $config['name']));
    }

    /**
     * Create an instance of any handler available in Monolog.
     *
     * @param array $config
     *
     * @throws \InvalidArgumentException
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createMonologDriver(array $config): LoggerInterface
    {
        if ($this->container === null) {
            throw new RuntimeException('No container instance was found.');
        }

        $config['name'] = $config['original_name'];

        unset($config['original_name']);

        if ($this->container->has($config['handler'])) {
            $handler = $this->container->get($config['handler']);

            if (! \is_a($handler, HandlerInterface::class, true)) {
                throw new InvalidArgumentException(\sprintf('[%s] must be an instance of [%s].', $config['handler'], HandlerInterface::class));
            }
        } else {
            throw new InvalidArgumentException(\sprintf('Handler [%s] is not managed by the container.', $config['handler']));
        }

        if (! isset($config['formatter'])) {
            $handler->setFormatter($this->getConfiguredLineFormatter());
        } elseif ($config['formatter'] !== 'default') {
            $handler->setFormatter($this->container->get($config['formatter']));
        }

        $monolog = new Monolog($this->parseChannel($config));

        $monolog->pushHandler($handler);

        return $monolog;
    }

    /**
     * Returns a line formatter with included stacktraces.
     *
     * @return \Monolog\Formatter\LineFormatter
     */
    protected function getConfiguredLineFormatter(): LineFormatter
    {
        $formatter = new LineFormatter(
            null,
            null,
            true,
            true
        );

        $formatter->includeStacktraces();

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigFromName(string $name): array
    {
        $config = parent::getConfigFromName($name);

        if (isset($config['driver']) && \in_array($config['driver'], ['custom', 'monolog'], true)) {
            $config['original_name'] = $config['name'];
            $config['name'] = $config['driver'];
        }

        return $config;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigName(): string
    {
        return 'logging';
    }

    /**
     * Push given processors to monolog.
     *
     * @param array           $config
     * @param \Monolog\Logger $driver
     *
     * @return \Monolog\Logger
     */
    protected function pushProcessorsToMonolog(array $config, Monolog $driver): Monolog
    {
        $processors = $this->processors;

        if (isset($config['processors'])) {
            $processors = \array_merge($processors, $config['processors']);
        }

        foreach ($processors as $processor) {
            $driver->pushProcessor($processor);
        }

        return $driver;
    }

    /**
     * Extract the log channel from the given configuration.
     *
     * @param array $config
     *
     * @return string
     */
    private function parseChannel(array $config): string
    {
        return $config['channel'] ?? $this->resolvedOptions['env'];
    }

    /**
     * Return the file path for some logger.
     *
     * @return string
     */
    private function getFilePath(): string
    {
        return $this->resolvedOptions['path'] . \DIRECTORY_SEPARATOR . $this->resolvedOptions['env'] . '.log';
    }
}
