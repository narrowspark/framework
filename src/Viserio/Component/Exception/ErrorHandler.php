<?php
declare(strict_types=1);
namespace Viserio\Component\Exception;

use Error;
use ErrorException;
use Exception;
use Narrowspark\HttpStatus\Exception\AbstractClientErrorException;
use Narrowspark\HttpStatus\Exception\AbstractServerErrorException;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\OutOfMemoryException;
use Throwable;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Exception\Transformer as TransformerContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Exception\Console\Handler as ConsoleHandler;
use Viserio\Component\Exception\Traits\DetermineErrorLevelTrait;
use Viserio\Component\Exception\Transformer\ClassNotFoundFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\UndefinedFunctionFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ErrorHandler implements
    RequiresComponentConfigContract,
    ProvidesDefaultOptionsContract,
    LoggerAwareInterface
{
    use ContainerAwareTrait;
    use OptionsResolverTrait;
    use LoggerAwareTrait;
    use DetermineErrorLevelTrait;

    /**
     * ExceptionIdentifier instance.
     *
     * @var \Viserio\Component\Exception\ExceptionIdentifier
     */
    protected $exceptionIdentifier;

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
     * @var null|int
     */
    private $reservedMemory;

    /**
     * List of int errors to string.
     *
     * @var array
     */
    private static $levels = [
        E_DEPRECATED        => 'Deprecated',
        E_USER_DEPRECATED   => 'User Deprecated',
        E_NOTICE            => 'Notice',
        E_USER_NOTICE       => 'User Notice',
        E_STRICT            => 'Runtime Notice',
        E_WARNING           => 'Warning',
        E_USER_WARNING      => 'User Warning',
        E_COMPILE_WARNING   => 'Compile Warning',
        E_CORE_WARNING      => 'Core Warning',
        E_USER_ERROR        => 'User Error',
        E_RECOVERABLE_ERROR => 'Catchable Fatal Error',
        E_COMPILE_ERROR     => 'Compile Error',
        E_PARSE             => 'Parse Error',
        E_ERROR             => 'Error',
        E_CORE_ERROR        => 'Core Error',
    ];

    /**
     * Transform's php errors to logger level errors.
     *
     * @var array
     */
    private static $loggers = [
        E_DEPRECATED        => LogLevel::INFO,
        E_USER_DEPRECATED   => LogLevel::INFO,
        E_NOTICE            => LogLevel::WARNING,
        E_USER_NOTICE       => LogLevel::WARNING,
        E_STRICT            => LogLevel::WARNING,
        E_WARNING           => LogLevel::WARNING,
        E_USER_WARNING      => LogLevel::WARNING,
        E_COMPILE_WARNING   => LogLevel::WARNING,
        E_CORE_WARNING      => LogLevel::WARNING,
        E_USER_ERROR        => LogLevel::CRITICAL,
        E_RECOVERABLE_ERROR => LogLevel::CRITICAL,
        E_COMPILE_ERROR     => LogLevel::CRITICAL,
        E_PARSE             => LogLevel::CRITICAL,
        E_ERROR             => LogLevel::CRITICAL,
        E_CORE_ERROR        => LogLevel::CRITICAL,
    ];

    /**
     * Create a new error handler instance.
     *
     * @param array|\ArrayAccess|\Psr\Container\ContainerInterface $data
     * @param null|\Psr\Log\LoggerInterface                        $logger
     */
    public function __construct($data, ?LoggerInterface $logger = null)
    {
        $this->resolvedOptions     = self::resolveOptions($data);
        $this->exceptionIdentifier = new ExceptionIdentifier();
        $this->transformers        = $this->transformArray($this->resolvedOptions['transformers']);
        $this->dontReport          = $this->resolvedOptions['dont_report'];
        $this->logger              = $logger ?? new NullLogger();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'exception'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
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
            'transformers' => [
                ClassNotFoundFatalErrorTransformer::class     => new ClassNotFoundFatalErrorTransformer(),
                UndefinedFunctionFatalErrorTransformer::class => new UndefinedFunctionFatalErrorTransformer(),
                UndefinedMethodFatalErrorTransformer::class   => new UndefinedMethodFatalErrorTransformer(),
            ],
        ];
    }

    /**
     * Determine if the exception shouldn't be reported.
     *
     * @param \Throwable $exception
     *
     * @return $this
     */
    public function addShouldntReport(Throwable $exception): self
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
        $id    = $this->exceptionIdentifier->identify($exception);

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
    public function addTransformer(TransformerContract $transformer): self
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
     * @throws \ErrorException
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
        if (error_reporting() === 0) {
            return false;
        }

        // Level is the current error reporting level to manage silent error.
        // Strong errors are not authorized to be silenced.
        $level = \error_reporting() | E_RECOVERABLE_ERROR | E_USER_ERROR | E_DEPRECATED | E_USER_DEPRECATED;

        if ($level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }

        return true;
    }

    /**
     * Handle an uncaught exception from the application.
     *
     * Note: Most exceptions can be handled via the try / catch block in
     * the HTTP and Console kernels. But, fatal error exceptions must
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

        $transformed = $this->getTransformed($exception);

        if ((PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg') &&
            \class_exists(ConsoleOutput::class)
        ) {
            (new ConsoleHandler())->render(new ConsoleOutput(), $transformed);
        }

        throw $transformed;
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

            if (0 === mb_strpos($error['message'], 'Allowed memory') || 0 === mb_strpos($error['message'], 'Out of memory')) {
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
        if (PHP_SAPI !== 'cli' || PHP_SAPI !== 'phpdbg') {
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
     * @param \Throwable $exception
     *
     * @return \Error|\Symfony\Component\Debug\FatalErrorHandler\FatalErrorHandlerInterface|\Throwable
     */
    protected function prepareException(Throwable $exception)
    {
        if (! $exception instanceof Exception) {
            return new FatalThrowableError($exception);
        }

        if ($exception instanceof Error) {
            return new FatalErrorException(
                $exception->getMessage(),
                $exception->getCode(),
                E_ERROR,
                $exception->getFile(),
                $exception->getLine(),
                $exception->getTrace()
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

        if (! $exception instanceof OutOfMemoryException ||
            count($transformers) === 0
        ) {
            return $exception;
        }

        foreach ($transformers as $transformer) {
            $exception = $transformer->transform($exception);
        }

        return $exception;
    }

    /**
     * Transform's the given array to a key (class name) value (object/class name) array.
     *
     * @return array
     */
    protected function transformArray(array $data): array
    {
        $array = [];

        foreach ($data as $key => $value) {
            if (is_numeric($key)) {
                $key = is_string($value) ? $value : \get_class($value);
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
     * @return object[]
     */
    protected function make(array $classes)
    {
        foreach ($classes as $index => $class) {
            if (is_object($class)) {
                $classes[$index] = $class;

                continue;
            }

            if ($this->container === null) {
                continue;
            }

            try {
                $classes[$index] = $this->container->get($class);
            } catch (NotFoundExceptionInterface $exception) {
                unset($classes[$index]);

                $this->report(
                    $exception instanceof Exception ? $exception : new FatalThrowableError($exception)
                );
            }
        }

        return array_values($classes);
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
