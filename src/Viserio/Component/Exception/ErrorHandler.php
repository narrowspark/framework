<?php
declare(strict_types=1);
namespace Viserio\Component\Exception;

use Error;
use ErrorException;
use Exception;
use Narrowspark\HttpStatus\Exception\AbstractClientErrorException;
use Narrowspark\HttpStatus\Exception\AbstractServerErrorException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\OutOfMemoryException;
use Throwable;
use Viserio\Component\Contract\Container\Exception\NotFoundException;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Exception\Handler as HandlerContract;
use Viserio\Component\Contract\Exception\Transformer as TransformerContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Exception\Traits\DetermineErrorLevelTrait;
use Viserio\Component\Exception\Transformer\ClassNotFoundFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\UndefinedFunctionFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ErrorHandler implements
    HandlerContract,
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    LoggerAwareInterface
{
    use ContainerAwareTrait;
    use OptionsResolverTrait;
    use LoggerAwareTrait;
    use DetermineErrorLevelTrait;

    /**
     * Exception transformers.
     *
     * @var array
     */
    protected $transformers = [];

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * Resolved options.
     *
     * @var array
     */
    protected $resolvedOptions = [];

    /**
     * Some reserved memory.
     *
     * @var null|string
     */
    private $reservedMemory;

    /**
     * List of int errors to string.
     *
     * @var array
     */
    private static $levels = [
        \E_DEPRECATED        => 'Deprecated',
        \E_USER_DEPRECATED   => 'User Deprecated',
        \E_NOTICE            => 'Notice',
        \E_USER_NOTICE       => 'User Notice',
        \E_STRICT            => 'Runtime Notice',
        \E_WARNING           => 'Warning',
        \E_USER_WARNING      => 'User Warning',
        \E_COMPILE_WARNING   => 'Compile Warning',
        \E_CORE_WARNING      => 'Core Warning',
        \E_USER_ERROR        => 'User Error',
        \E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        \E_COMPILE_ERROR     => 'Compile Error',
        \E_PARSE             => 'Parse Error',
        \E_ERROR             => 'Error',
        \E_CORE_ERROR        => 'Core Error',
    ];

    /**
     * Transform's php errors to logger level errors.
     *
     * @var array
     */
    private static $loggers = [
        \E_DEPRECATED        => LogLevel::INFO,
        \E_USER_DEPRECATED   => LogLevel::INFO,
        \E_NOTICE            => LogLevel::WARNING,
        \E_USER_NOTICE       => LogLevel::WARNING,
        \E_STRICT            => LogLevel::WARNING,
        \E_WARNING           => LogLevel::WARNING,
        \E_USER_WARNING      => LogLevel::WARNING,
        \E_COMPILE_WARNING   => LogLevel::WARNING,
        \E_CORE_WARNING      => LogLevel::WARNING,
        \E_USER_ERROR        => LogLevel::CRITICAL,
        \E_RECOVERABLE_ERROR => LogLevel::CRITICAL,
        \E_COMPILE_ERROR     => LogLevel::CRITICAL,
        \E_PARSE             => LogLevel::CRITICAL,
        \E_ERROR             => LogLevel::CRITICAL,
        \E_CORE_ERROR        => LogLevel::CRITICAL,
    ];

    /**
     * Create a new error handler instance.
     *
     * @param array|\ArrayAccess            $config
     * @param null|\Psr\Log\LoggerInterface $logger
     */
    public function __construct($config, ?LoggerInterface $logger = null)
    {
        $this->resolvedOptions = self::resolveOptions($config);
        $this->transformers    = \array_merge(
            $this->getErrorTransformer(),
            $this->transformArray($this->resolvedOptions['transformers'])
        );

        $this->dontReport = $this->resolvedOptions['dont_report'];
        $this->logger     = $logger ?? new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return ['viserio', 'exception'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return [
            // A list of the exception types that should not be reported.
            'dont_report' => [],
            'levels'      => [
                FatalThrowableError::class          => LogLevel::CRITICAL,
                FatalErrorException::class          => LogLevel::ERROR,
                Throwable::class                    => LogLevel::ERROR,
                Exception::class                    => LogLevel::ERROR,
                AbstractClientErrorException::class => LogLevel::NOTICE,
                AbstractServerErrorException::class => LogLevel::ERROR,
            ],
            // Exception transformers.
            'transformers' => [],
        ];
    }

    /**
     * Determine if the exception shouldn't be reported.
     *
     * @param \Throwable $exception
     *
     * @return $this
     */
    public function addShouldntReport(Throwable $exception): HandlerContract
    {
        $this->dontReport[\get_class($exception)] = $exception;

        return $this;
    }

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function report(Throwable $exception): void
    {
        if ($this->shouldntReport($exception)) {
            return;
        }

        $level = $this->getLevel($exception);
        $id    = ExceptionIdentifier::identify($exception);

        if ($exception instanceof FatalErrorException) {
            if ($exception instanceof FatalThrowableError) {
                $message = $exception->getMessage();
            } else {
                $message = 'Fatal ' . $exception->getMessage();
            }
        } elseif ($exception instanceof ErrorException) {
            $message = 'Uncaught ' . $exception->getMessage();
        } else {
            $message = 'Uncaught Exception: ' . $exception->getMessage();
        }

        $this->logger->{$level}(
            $message,
            ['exception' => $exception, 'identification' => ['id' => $id]]
        );
    }

    /**
     * Add the transformed instance.
     *
     * @param \Viserio\Component\Contract\Exception\Transformer $transformer
     *
     * @return $this
     */
    public function addTransformer(TransformerContract $transformer): HandlerContract
    {
        $this->transformers[\get_class($transformer)] = $transformer;

        return $this;
    }

    /**
     * Get the transformer exceptions.
     *
     * @return array
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * Convert errors into ErrorException objects.
     *
     * This method catches PHP errors and converts them into ErrorException objects;
     * these ErrorException objects are then thrown and caught by Viserio's
     * built-in or custom error handlers.
     *
     * @param int    $type    The numeric type of the Error
     * @param string $message The error message
     * @param string $file    The absolute path to the affected file
     * @param int    $line    The line number of the error in the affected file
     *
     * @throws \Symfony\Component\Debug\Exception\FatalErrorException
     *
     * @return bool Returns false when no handling happens so that the PHP engine can handle the error itself
     *
     * @internal
     */
    public function handleError(
        int $type,
        string $message,
        string $file = '',
        int $line = 0
    ): bool {
        if (\error_reporting() === 0) {
            return false;
        }

        // Level is the current error reporting level to manage silent error.
        // Strong errors are not authorized to be silenced.
        $severity = \error_reporting() | \E_RECOVERABLE_ERROR | \E_USER_ERROR | \E_DEPRECATED | \E_USER_DEPRECATED;

        if ($severity) {
            throw new FatalErrorException($message, 0, $severity, $file, $line);
        }

        return true;
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Fatal error exceptions must
     * be handled differently since they are not normal exceptions.
     *
     * @param \Throwable $exception
     *
     * @throws \Throwable
     *
     * @return void
     *
     * @internal
     *
     * @see https://secure.php.net/manual/en/function.set-exception-handler.php
     */
    public function handleException(Throwable $exception): void
    {
        $exception = $this->prepareException($exception);

        try {
            $this->report($exception);
        } catch (Throwable $e) {
            // If handler can't report exception just throw it
        }

        throw $this->getTransformed($exception);
    }

    /**
     * Shutdown registered function for handling PHP fatal errors.
     *
     * @throws \Throwable
     *
     * @return void
     *
     * @internal
     */
    public function handleShutdown(): void
    {
        if ($this->reservedMemory === null) {
            return;
        }

        $this->reservedMemory = null;

        // If an error has occurred that has not been displayed, we will create a fatal
        // error exception instance and pass it into the regular exception handling
        // code so it can be displayed back out to the developer for information.
        $error     = \error_get_last();
        $exception = null;

        if ($error !== null && self::isLevelFatal($error['type'])) {
            $trace = $error['backtrace'] ?? null;

            if (\mb_strpos($error['message'], 'Allowed memory') === 0 || \mb_strpos($error['message'], 'Out of memory') === 0) {
                $exception = new OutOfMemoryException(self::$levels[$error['type']] . ': ' . $error['message'], 0, $error['type'], $error['file'], $error['line'], 2, false, $trace);
            } else {
                // Create a new fatal exception instance from an error array.
                $exception = new FatalErrorException(self::$levels[$error['type']] . ': ' . $error['message'], 0, $error['type'], $error['file'], $error['line'], 2, true, $trace);
            }
        }

        if ($exception !== null) {
            $this->handleException($exception);
        }
    }

    /**
     * Register the PHP error handler.
     *
     * @return void
     */
    protected function registerErrorHandler(): void
    {
        \set_error_handler([$this, 'handleError']);
    }

    /**
     * Register the PHP exception handler.
     *
     * @return void
     */
    protected function registerExceptionHandler(): void
    {
        if (\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            \ini_set('display_errors', '0');
        } elseif (! \ini_get('log_errors') || \ini_get('error_log')) {
            // CLI - display errors only if they're not already logged to STDERR
            \ini_set('display_errors', '1');
        }

        \set_exception_handler([$this, 'handleException']);
    }

    /**
     * Register the PHP shutdown handler.
     *
     * @return void
     */
    protected function registerShutdownHandler(): void
    {
        if ($this->reservedMemory === null) {
            $this->reservedMemory = \str_repeat('x', 10240);
            \register_shutdown_function([$this, 'handleShutdown']);
        }
    }

    /**
     * Prepare exception in a fatal error handler.
     *
     * @param \Error|\Exception|\Throwable $exception
     *
     * @return \Error|\Symfony\Component\Debug\FatalErrorHandler\FatalErrorHandlerInterface|\Throwable
     */
    protected function prepareException($exception)
    {
        if (! $exception instanceof Exception && ! $exception instanceof Error) {
            $exception = new FatalThrowableError($exception);
        } elseif ($exception instanceof Error) {
            $trace = $exception->getTrace();

            $exception = new FatalErrorException(
                $exception->getMessage(),
                $exception->getCode(),
                \E_ERROR,
                $exception->getFile(),
                $exception->getLine(),
                \count($trace),
                \count($trace) !== 0,
                $trace
            );
        }

        return $exception;
    }

    /**
     * Get the transformed exception.
     *
     * @param \Throwable $exception
     *
     * @return \Throwable
     */
    protected function getTransformed(Throwable $exception): Throwable
    {
        $transformers = $this->make($this->transformers);

        if (! $exception instanceof OutOfMemoryException || \count($transformers) === 0) {
            return $exception;
        }

        foreach ($transformers as $transformer) {
            /** @var TransformerContract $transformer */
            $exception = $transformer->transform($exception);
        }

        return $exception;
    }

    /**
     * Transform's the given array to a key (class name) value (object/class name) array.
     *
     * @param array $data
     *
     * @return array
     */
    protected function transformArray(array $data): array
    {
        $array = [];

        foreach ($data as $key => $value) {
            if (\is_numeric($key)) {
                $key = \is_string($value) ? $value : \get_class($value);
            }

            $array[$key] = $value;
        }

        return $array;
    }

    /**
     * Make multiple objects using the container
     * if the value is not a object.
     *
     * @param array $classes
     *
     * @return array
     */
    protected function make(array $classes): array
    {
        foreach ($classes as $index => $class) {
            if (\is_object($class)) {
                $classes[$index] = $class;

                continue;
            }

            if ($this->container === null) {
                continue;
            }

            if (! $this->container->has($class)) {
                unset($classes[$index]);

                $this->report(new NotFoundException(\sprintf('Class [%s] not found.', $class)));
            } else {
                $classes[$index] = $this->container->get($class);
            }
        }

        return \array_values($classes);
    }

    /**
     * The default error transformers.
     *
     * @return array
     */
    protected function getErrorTransformer(): array
    {
        return [
            ClassNotFoundFatalErrorTransformer::class     => new ClassNotFoundFatalErrorTransformer(),
            UndefinedFunctionFatalErrorTransformer::class => new UndefinedFunctionFatalErrorTransformer(),
            UndefinedMethodFatalErrorTransformer::class   => new UndefinedMethodFatalErrorTransformer(),
        ];
    }

    /**
     * Get the exception level.
     *
     * @param \Throwable $exception
     *
     * @return string
     */
    private function getLevel(Throwable $exception): string
    {
        foreach ($this->resolvedOptions['levels'] as $class => $level) {
            if ($exception instanceof $class) {
                return $level;
            }
        }

        if ($exception instanceof FatalErrorException) {
            return self::$loggers[$exception->getSeverity()];
        }

        return LogLevel::ERROR;
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Throwable $exception
     *
     * @return bool
     */
    private function shouldntReport(Throwable $exception): bool
    {
        foreach ($this->dontReport as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }

        return false;
    }
}
