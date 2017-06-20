<?php
declare(strict_types=1);
namespace Viserio\Component\Exception;

use Error;
use ErrorException;
use Exception;
use Narrowspark\HttpStatus\Exception\AbstractClientErrorException;
use Narrowspark\HttpStatus\Exception\AbstractServerErrorException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Throwable;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Exception\Transformer as TransformerContract;
use Viserio\Component\Contracts\Log\Traits\LoggerAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Exception\Transformer\ClassNotFoundFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\CommandLineTransformer;
use Viserio\Component\Exception\Transformer\UndefinedFunctionFatalErrorTransformer;
use Viserio\Component\Exception\Transformer\UndefinedMethodFatalErrorTransformer;
use Viserio\Component\OptionsResolver\Traits\OptionsResolverTrait;

class ErrorHandler implements RequiresComponentConfigContract, ProvidesDefaultOptionsContract
{
    use ContainerAwareTrait;
    use OptionsResolverTrait;
    use LoggerAwareTrait;

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
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container           = $container;
        $this->exceptionIdentifier = new ExceptionIdentifier();

        if ($this->container->has(LoggerInterface::class)) {
            $this->logger = $this->container->get(LoggerInterface::class);
        } else {
            $this->logger = new NullLogger();
        }

        $this->resolvedOptions = $this->resolveOptions($this->container);
    }

    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'exception'];
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultOptions(): iterable
    {
        return [
            // A list of the exception types that should not be reported.
            'dont_report' => [],
            'levels'      => [
                FatalThrowableError::class          => 'critical',
                FatalErrorException::class          => 'error',
                Throwable::class                    => 'error',
                Exception::class                    => 'error',
                NotFoundException::class            => 'notice',
                AbstractClientErrorException::class => 'notice',
                AbstractServerErrorException::class => 'error',
            ],
            // Exception transformers.
            'transformers' => [
                ClassNotFoundFatalErrorTransformer::class,
                CommandLineTransformer::class,
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
        $this->dontReport[get_class($exception)] = $exception;

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

        $this->getLogger()->{$level}(
            $exception->getMessage(),
            ['exception' => $exception, 'identification' => ['id' => $id]]
        );
    }

    /**
     * Add the transformed instance.
     *
     * @param \Viserio\Component\Contracts\Exception\Transformer $transformer
     *
     * @return $this
     */
    public function addTransformer(TransformerContract $transformer): self
    {
        $this->transformers[get_class($transformer)] = $transformer;

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
     * @param array|null $backtrace
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
        $level = error_reporting() | E_RECOVERABLE_ERROR | E_USER_ERROR | E_DEPRECATED | E_USER_DEPRECATED;

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

        $this->report($exception);

        $transformed = $this->getTransformed($exception);
        $container   = $this->container;

        if (PHP_SAPI === 'cli') {
            if ($container->has(ConsoleApplication::class)) {
                $container->get(ConsoleApplication::class)
                    ->renderException($transformed, new ConsoleOutput());
            } else {
                throw $transformed;
            }
        }

        throw $exception;
    }

    /**
     * Shutdown registered function for handling PHP fatal errors.
     *
     * @internal
     */
    public function handleShutdown()
    {
        // If an error has occurred that has not been displayed, we will create a fatal
        // error exception instance and pass it into the regular exception handling
        // code so it can be displayed back out to the developer for information.
        $error = error_get_last();

        if ($error !== null && $this->isFatal($error['type'])) {
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
     * Determine if the error type is fatal.
     *
     * @param int $type
     *
     * @return bool
     *
     * @codeCoverageIgnore
     */
    protected function isFatal(int $type): bool
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true);
    }

    /**
     * Register the PHP error handler.
     *
     * @return void
     */
    protected function registerErrorHandler(): void
    {
        set_error_handler([$this, 'handleError']);
    }

    /**
     * Register the PHP exception handler.
     *
     * @return void
     */
    protected function registerExceptionHandler(): void
    {
        if (php_sapi_name() != 'cli' || php_sapi_name() != 'phpdbg') {
            ini_set('display_errors', '0');
        } elseif (! ini_get('log_errors') || ini_get('error_log')) {
            // CLI - display errors only if they're not already logged to STDERR
            ini_set('display_errors', '1');
        }

        set_exception_handler([$this, 'handleException']);
    }

    /**
     * Register the PHP shutdown handler.
     *
     * @return void
     */
    protected function registerShutdownHandler(): void
    {
        register_shutdown_function([$this, 'handleShutdown']);
    }

    /**
     * Prepare exception in a fatal error handler.
     *
     * @param \Throwable $exception
     *
     * @return \Symfony\Component\Debug\FatalErrorHandler\FatalErrorHandlerInterface|\Throwable|\Error
     */
    protected function prepareException(Throwable $exception)
    {
        if (! $exception instanceof Exception) {
            $exception = new FatalThrowableError($exception);
        } elseif ($exception instanceof Error) {
            $exception = new FatalErrorException(
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
     * @throws \RuntimeException if transformer is not found
     *
     * @return \Throwable
     */
    protected function getTransformed(Throwable $exception)
    {
        $container    = $this->container;
        $transformers = array_merge(
            $this->transformers,
            $this->resolvedOptions['transformers']
        );

        foreach ($transformers as $transformer) {
            if (is_object($transformer)) {
                $transformerClass = $transformer;
            } elseif ($container->has($transformer)) {
                $transformerClass = $container->get($transformer);
            } else {
                throw new RuntimeException(sprintf('Transformer [%s] not found.', (string) $transformer));
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
    protected function getLevel(Throwable $exception): string
    {
        foreach ($this->resolvedOptions['levels'] as $class => $level) {
            if ($exception instanceof $class) {
                return $level;
            }
        }

        return 'error';
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Throwable $exception
     *
     * @return bool
     */
    protected function shouldntReport(Throwable $exception): bool
    {
        $dontReport = array_merge(
            $this->dontReport,
            $this->resolvedOptions['dont_report']
        );

        foreach ($dontReport as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigClass(): RequiresConfigContract
    {
        return $this;
    }
}
