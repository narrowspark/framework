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
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;
use Viserio\Component\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contract\Exception\Exception\NotFoundException as BaseNotFoundException;
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
     * Create a new error handler instance.
     *
     * @param array|\ArrayAccess|\Psr\Container\ContainerInterface $data
     * @param null|\Psr\Log\LoggerInterface                        $logger
     */
    public function __construct($data, ?LoggerInterface $logger = null)
    {
        $this->resolvedOptions     = self::resolveOptions($data);
        $this->exceptionIdentifier = new ExceptionIdentifier();
        $this->transformers        = $this->getFormattedTransformers();
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
                ClassNotFoundFatalErrorTransformer::class,
                UndefinedFunctionFatalErrorTransformer::class,
                UndefinedMethodFatalErrorTransformer::class,
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

        $this->logger->{$level}(
            $exception->getMessage(),
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
     * @param int        $type      The numeric type of the Error
     * @param string     $message   The error message
     * @param string     $file      The absolute path to the affected file
     * @param int        $line      The line number of the error in the affected file
     * @param null       $context
     * @param null|array $backtrace
     *
     * @throws \ErrorException
     *
     * @return void
     *
     * @internal
     */
    public function handleError(
        int $type,
        string $message,
        string $file = '',
        int $line = 0,
        $context = null,
        array $backtrace = null
    ): void {
        // Level is the current error reporting level to manage silent error.
        // Strong errors are not authorized to be silenced.
        $level = \error_reporting() | E_RECOVERABLE_ERROR | E_USER_ERROR | E_DEPRECATED | E_USER_DEPRECATED;

        if ($level) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
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
     */
    public function handleException(Throwable $exception): void
    {
        $exception = $this->prepareException($exception);

        try {
            $this->report($exception);
        } catch (Throwable $exception) {
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
        // If an error has occurred that has not been displayed, we will create a fatal
        // error exception instance and pass it into the regular exception handling
        // code so it can be displayed back out to the developer for information.
        $error = \error_get_last();

        if ($error !== null && self::isLevelFatal($error['type'])) {
            $this->handleException(
                // Create a new fatal exception instance from an error array.
                new FatalErrorException(
                    $error['message'],
                    $error['type'],
                    0,
                    $error['file'],
                    $error['line'],
                    0
                )
            );
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
        \register_shutdown_function([$this, 'handleShutdown']);
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
     * @throws \Viserio\Component\Contract\Exception\Exception\NotFoundException if transformer is not found
     *
     * @return \Throwable
     */
    protected function getTransformed(Throwable $exception): Throwable
    {
        foreach ($this->transformers as $transformer) {
            if (\is_object($transformer)) {
                $transformerClass = $transformer;
            } elseif ($this->container !== null && $this->container->has($transformer)) {
                $transformerClass = $this->container->get($transformer);
            } else {
                throw new BaseNotFoundException(\sprintf('Transformer [%s] not found.', (string) $transformer));
            }

            $exception = $transformerClass->transform($exception);
        }

        return $exception;
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

    /**
     * Formant's the filters array to a key (class name) value (object/class name).
     *
     * @return array
     */
    private function getFormattedTransformers(): array
    {
        $transformers = [];

        foreach ($this->resolvedOptions['transformers'] as $key => $transformer) {
            if (is_numeric($key)) {
                $key = is_string($transformer) ? $transformer : \get_class($transformer);
            }

            $transformers[$key] = $transformer;
        }

        return $transformers;
    }
}
