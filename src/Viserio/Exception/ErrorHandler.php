<?php
declare(strict_types=1);
namespace Viserio\Exception;

use Error;
use ErrorException;
use Narrowspark\HttpStatus\Exception\AbstractClientErrorException;
use Narrowspark\HttpStatus\Exception\AbstractServerErrorException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use RuntimeException;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\OutOfMemoryException;
use Symfony\Component\Debug\FatalErrorHandler\ClassNotFoundFatalErrorHandler;
use Symfony\Component\Debug\FatalErrorHandler\UndefinedFunctionFatalErrorHandler;
use Symfony\Component\Debug\FatalErrorHandler\UndefinedMethodFatalErrorHandler;
use Throwable;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Exception\Transformer as TransformerContract;
use Viserio\Contracts\Log\Traits\LoggerAwareTrait;
use Viserio\Exception\Transformers\CommandLineTransformer;
use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;

class ErrorHandler implements RequiresConfig
{
    use ConfigurationTrait;
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /**
     * Exception transformers.
     *
     * @var array
     */
    protected $transformers = [
        CommandLineTransformer::class,
    ];

    /**
     * Array of fatal error handlers.
     *
     * Override this variable if you want to define more fatal error handlers.
     *
     * @return \Symfony\Component\Debug\FatalErrorHandler\FatalErrorHandlerInterface[]
     */
    protected $fatalErrorHandlers = [
        UndefinedFunctionFatalErrorHandler::class,
        UndefinedMethodFatalErrorHandler::class,
        ClassNotFoundFatalErrorHandler::class,
    ];

    /**
     * Exception levels.
     *
     * @var array
     */
    protected $defaultLevels = [
        FatalThrowableError::class          => 'critical',
        FatalErrorException::class          => 'error',
        Throwable::class                    => 'error',
        NotFoundException::class            => 'notice',
        AbstractClientErrorException::class => 'notice',
        AbstractServerErrorException::class => 'error',
    ];

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * {@inheritdoc}
     */
    public function dimensions(): iterable
    {
        return ['viserio', 'exception'];
    }

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions(): iterable
    {
        return ['driverClass', 'params'];
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
        $this->dontReport[] = $exception;

        return $this;
    }

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     *
     * @return void|null
     */
    public function report(Throwable $exception)
    {
        if ($this->shouldntReport($exception)) {
            return;
        }

        $level = $this->getLevel($exception);
        $id    = $this->exceptionIdentifier->identify($exception);

        if ($this->logger !== null) {
            $this->getLogger()->{$level}($exception, ['identification' => ['id' => $id]]);
        }
    }

    /**
     * Add the transformed instance.
     *
     * @param \Viserio\Contracts\Exception\Transformer $transformer
     *
     * @return $this
     */
    public function addTransformer(TransformerContract $transformer): self
    {
        $transformerClass = is_object($transformer) ? get_class($transformer) : $transformer;

        if (in_array($transformerClass, $this->transformers)) {
            $pos = array_search($transformerClass, $this->transformers);

            unset($this->transformers[$pos]);
        }

        $this->transformers[] = $transformer;

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
     * @return bool Returns false when no handling happens so that the PHP engine can handle the error itself
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
     * @param \Throwable|\Exception $exception
     *
     * @return void|string
     *
     * @internal
     */
    public function handleException($exception)
    {
        $exception = $this->prepareAndWrapException($exception);

        $this->report($exception);

        $transformed = $this->getTransformed($exception);
        $container   = $this->container;

        if (PHP_SAPI === 'cli') {
            if ($container->has(ConsoleApplication::class)) {
                $container->get(ConsoleApplication::class)
                    ->renderException($transformed, new ConsoleOutput());
            } else {
                throw $exception;
            }
        } else {
            $response = $this->getPreparedResponse(
                $container,
                $exception,
                $transformed
            );

            return (string) $response->getBody();
        }
    }

    /**
     * Handle the PHP shutdown event.
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
        if (PHP_SAPI !== 'cli') {
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
     * Prepare and wrap exception in a fatal error handler.
     *
     * @param \Throwable|\Exception $exception
     *
     * @return \Symfony\Component\Debug\FatalErrorHandler\FatalErrorHandlerInterface|\Throwable|\Error
     */
    protected function prepareAndWrapException($exception)
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

        if ($exception instanceof FatalErrorException && ! $exception instanceof OutOfMemoryException) {
            $error = [
                'type'    => $exception->getSeverity(),
                'message' => $exception->getMessage(),
                'file'    => $exception->getFile(),
                'line'    => $exception->getLine(),
            ];

            foreach ($this->fatalErrorHandlers as $handler) {
                if ($e = (new $handler())->handleError($error, $exception)) {
                    $exception = $e;
                    break;
                }
            }
        }

        return $exception;
    }

    /**
     * Get the transformed exception.
     *
     * @param \Throwable|\Exception $exception
     *
     * @throws \RuntimeException If transformer is not found.
     *
     * @return \Throwable|\Exception
     */
    protected function getTransformed($exception)
    {
        $container    = $this->container;
        $transformers = $this->transformers;

        if ($container->has(RepositoryContract::class)) {
            $transformers = array_merge(
                $transformers,
                $container->get(RepositoryContract::class)->get('exception.transformers', [])
            );
        }

        foreach ($transformers as $transformer) {
            if (is_object($transformer)) {
                $transformerClass = $transformer;
            } elseif ($container->has($transformer)) {
                $transformerClass = $container->get($transformer);
            } else {
                throw new RuntimeException('');
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
        $levels = $this->defaultLevels;

        if ($this->container->has(RepositoryContract::class)) {
            $levels = array_merge(
                $levels,
                $this->container->get(RepositoryContract::class)->get('exception.levels', [])
            );
        }

        foreach ($levels as $class => $level) {
            if ($exception instanceof $class) {
                return $level;
            }
        }

        return 'error';
    }
}
