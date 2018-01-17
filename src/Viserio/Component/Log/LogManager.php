<?php
declare(strict_types=1);
namespace Viserio\Component\Log;

use InvalidArgumentException;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Monolog\Logger as Monolog;
use Viserio\Component\Contract\Events\Traits\EventManagerAwareTrait;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Log\Traits\ParseLevelTrait;
use Viserio\Component\Support\AbstractManager;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;

class LogManager extends AbstractManager implements
    LoggerInterface,
    RequiresMandatoryOptionsContract,
    ProvidesDefaultOptionsContract
{
    use LoggerTrait;
    use ParseLevelTrait;
    use EventManagerAwareTrait;

    /**
     * {@inheritdoc}
     */
    protected const DRIVERS_CONFIG_LIST_NAME = 'channels';

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return [
            'default'   => 'single',
            'env'       => 'production',
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
                    'days' => 3,
                ],
                'syslog' => [
                    'driver' => 'syslog',
                    'level' => 'debug',
                ],
                'errorlog' => [
                    'driver' => 'errorlog',
                    'level' => 'debug',
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['path'];
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
        $this->getDriver()->log($level, $message, $context);
    }

    /**
     * Create an emergency log handler to avoid white screens of death.
     *
     * @var array $config
     *
     * @return \Psr\Log\LoggerInterface
     */
    protected function createEmergencyLogger($config)
    {
        $handler = new HandlerParser(new Monolog($config['name']));
        $formatter = new LineFormatter(
            HandlerParser::getLineFormatterSettings(),
            null,
            true,
            true
        );

        return $handler->parseHandler(
            'stream',
            $config['path'] . '/' . $config['name'] . '.log',
            'debug',
            null,
            $formatter
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createDriver(array $config): LoggerInterface
    {
        try {
            $driver = parent::createDriver($config);
        } catch (InvalidArgumentException $exception) {
            $driver = $this->createEmergencyLogger($config);
            $driver->emergency(
                'Unable to create configured logger. Using emergency logger.',
                ['exception' => $exception]
            );
        }

        $logger = new Logger($driver);

        if ($this->eventManager !== null) {
            $logger->setEventManager($this->eventManager);
        }

        return $logger;
    }

    /**
     * Extract the log channel from the given configuration.
     *
     * @param array $config
     *
     * @return string
     */
    protected function parseChannel(array $config): string
    {
        return $config['channel'] ?? $this->resolvedOptions['env'];
    }

    /**
     * {@inheritdoc}
     */
    protected static function getConfigName(): string
    {
        return 'logging';
    }
}
