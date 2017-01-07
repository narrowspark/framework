<?php
declare(strict_types=1);
namespace Viserio\Log;

use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger as MonologLogger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerTrait;
use Viserio\Contracts\Events\Traits\EventsAwareTrait;
use Viserio\Contracts\Log\Log as LogContract;
use Viserio\Contracts\Support\Arrayable;
use Viserio\Contracts\Support\Jsonable;
use Viserio\Log\Traits\ParseLevelTrait;

class Writer implements LogContract
{
    use ParseLevelTrait;
    use EventsAwareTrait;
    use LoggerTrait;

    /**
     * The handler parser instance.
     *
     * @var HandlerParser
     */
    protected $handlerParser;

    /**
     * Create a new log writer instance.
     *
     * @param \Monolog\Logger $monolog
     */
    public function __construct(MonologLogger $monolog)
    {
        // PSR 3 log message formatting for all handlers
        $monolog->pushProcessor(new PsrLogMessageProcessor());

        $this->handlerParser = new HandlerParser($monolog);
    }

    /**
     * Call Monolog with the given method and parameters.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return call_user_func_array([$this->getMonolog(), $method], $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function useFiles(
        string $path,
        string $level = 'debug',
        $processor = null,
        $formatter = null
    ) {
        $this->handlerParser->parseHandler(
            'stream',
            $path,
            $level,
            $processor,
            $formatter
        );
    }

    /**
     * {@inheritdoc}
     */
    public function useDailyFiles(
        string $path,
        int $days = 0,
        string $level = 'debug',
        $processor = null,
        $formatter = null
    ) {
        $this->handlerParser->parseHandler(
            new RotatingFileHandler($path, $days, $this->parseLevel($level)),
            '',
            '',
            $processor,
            $formatter
        );
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $message = $this->formatMessage($message);

        if ($this->events !== null) {
            // If the event dispatcher is set, we will pass along the parameters to the
            // log listeners. These are useful for building profilers or other tools
            // that aggregate all of the log messages for a given "request" cycle.
            $this->getEventManager()->trigger('viserio.log', compact('level', 'message', 'context'));
        }

        $this->getMonolog()->{$level}($message, $context);
    }

    /**
     * Get the underlying Monolog instance.
     *
     * @return \Monolog\Logger
     */
    public function getMonolog(): MonologLogger
    {
        return $this->handlerParser->getMonolog();
    }

    /**
     * Get the handler parser instance.
     *
     * @return \Viserio\Log\HandlerParser
     *
     * @codeCoverageIgnore
     */
    public function getHandlerParser(): HandlerParser
    {
        return $this->handlerParser;
    }

    /**
     * Format the parameters for the logger.
     *
     * @param mixed $message
     *
     * @return string|object|int|float|null|bool
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        // @codeCoverageIgnoreStart
        } elseif ($message instanceof Jsonable) {
            // @codeCoverageIgnoreEnd
            return $message->toJson();
        // @codeCoverageIgnoreStart
        } elseif ($message instanceof Arrayable) {
            // @codeCoverageIgnoreEnd
            return var_export($message->toArray(), true);
        }

        return $message;
    }
}
