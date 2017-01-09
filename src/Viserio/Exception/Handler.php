<?php
declare(strict_types=1);
namespace Viserio\Exception;

use Error;
use ErrorException;
use Interop\Container\ContainerInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Narrowspark\HttpStatus\Exception\AbstractClientErrorException;
use Narrowspark\HttpStatus\Exception\AbstractServerErrorException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Debug\Exception\OutOfMemoryException;
use Symfony\Component\Debug\FatalErrorHandler\ClassNotFoundFatalErrorHandler;
use Symfony\Component\Debug\FatalErrorHandler\UndefinedFunctionFatalErrorHandler;
use Symfony\Component\Debug\FatalErrorHandler\UndefinedMethodFatalErrorHandler;
use Throwable;
use Viserio\Contracts\Config\Repository as RepositoryContract;
use Viserio\Contracts\Config\Traits\ConfigAwareTrait;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Contracts\Exception\Filter as FilterContract;
use Viserio\Contracts\Exception\Handler as HandlerContract;
use Viserio\Contracts\Exception\Transformer as TransformerContract;
use Viserio\Contracts\Log\Traits\LoggerAwareTrait;
use Viserio\Exception\Displayers\HtmlDisplayer;
use Viserio\Exception\Filters\CanDisplayFilter;
use Viserio\Exception\Filters\VerboseFilter;
use Viserio\Exception\Transformers\CommandLineTransformer;

class Handler implements HandlerContract
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;
    use LoggerAwareTrait;

    /**
     * Exception displayers.
     *
     * @var array
     */
    protected $displayers = [];

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
     * Exception transformers.
     *
     * @var array
     */
    protected $transformers = [
        CommandLineTransformer::class,
    ];

    /**
     * Exception filters.
     *
     * @var array
     */
    protected $filters = [
        VerboseFilter::class,
        CanDisplayFilter::class,
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
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * ExceptionIdentifier instance.
     *
     * @var \Viserio\Exception\ExceptionIdentifier
     */
    protected $exceptionIdentifier;

    /**
     * Create a new exception handler instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container           = $container;
        $this->exceptionIdentifier = new ExceptionIdentifier();

        if ($this->container->has(LoggerInterface::class)) {
            $this->logger = $this->container->get(LoggerInterface::class);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addDisplayer(DisplayerContract $displayer): HandlerContract
    {
        if (in_array($displayer, $this->displayers)) {
            $pos = array_search($displayer, $this->displayers);

            unset($this->displayers[$pos]);
        }

        $this->displayers[] = $displayer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisplayers(): array
    {
        return $this->displayers;
    }

    /**
     * {@inheritdoc}
     */
    public function addTransformer(TransformerContract $transformer): HandlerContract
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
     * {@inheritdoc}
     */
    public function getTransformers(): array
    {
        return $this->transformers;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(FilterContract $filter): HandlerContract
    {
        $filterClass = is_object($filter) ? get_class($filter) : $filter;

        if (in_array($filterClass, $this->filters)) {
            $pos = array_search($filterClass, $this->filters);

            unset($this->filters[$pos]);
        }

        $this->filters[] = $filter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function addShouldntReport(Throwable $exception): HandlerContract
    {
        $this->dontReport[] = $exception;

        return $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function register()
    {
        error_reporting(E_ALL);

        $this->registerErrorHandler();

        // The DebugClassLoader attempts to throw more helpful exceptions
        // when a class isn't found by the registered autoloaders.
        DebugClassLoader::enable();

        $this->registerExceptionHandler();

        if ($this->getContainer()->get(RepositoryContract::class)->get('exception.env', null) !== 'testing') {
            $this->registerShutdownHandler();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unregister()
    {
        restore_error_handler();
    }

    /**
     * {@inheritdoc}
     */
    public function handleError(
        int $level,
        string $message,
        string $file = '',
        int $line = 0,
        $context = null
    ): void {
        if ($level & error_reporting()) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
                new FatalThrowableError(
                    new FatalErrorException(
                        $error['message'],
                        $error['type'],
                        0,
                        $error['file'],
                        $error['line'],
                        0
                    )
                )
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $transformed = $this->getTransformed($exception);

        return $this->getPreparedResponse(
            $this->getContainer(),
            $exception,
            $transformed
        );
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
            $this->getContainer()->get(RepositoryContract::class)->get('shouldnt_report', [])
        );

        foreach ($dontReport as $type) {
            if ($exception instanceof $type) {
                return true;
            }
        }

        return false;
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
        $levels = array_merge(
            $this->defaultLevels,
            $this->getContainer()->get(RepositoryContract::class)->get('exception.levels', [])
        );

        foreach ($levels as $class => $level) {
            if ($exception instanceof $class) {
                return $level;
            }
        }

        return 'error';
    }

    /**
     * Create a response for the given exception.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable|\Exception                    $exception
     * @param \Throwable|\Exception                    $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponse(
        ServerRequestInterface $request,
        $exception,
        $transformed
    ): ResponseInterface {
        $id          = $this->exceptionIdentifier->identify($exception);
        $transformed = $this->prepareAndWrapException($transformed);
        $flattened   = FlattenException::create($transformed);
        $code        = $flattened->getStatusCode();
        $headers     = $flattened->getHeaders();

        return $this->getDisplayer(
            $request,
            $exception,
            $transformed,
            $code
        )->display($transformed, $id, $code, $headers);
    }

    /**
     * Get the displayer instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $original
     * @param \Throwable                               $transformed
     * @param int                                      $code
     *
     * @return \Viserio\Contracts\Exception\Displayer
     */
    protected function getDisplayer(
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): DisplayerContract {
        $config = $this->getContainer()->get(RepositoryContract::class);

        $displayers = array_merge(
            $this->displayers,
            $config->get('exception.displayers', [])
        );

        if ($filtered = $this->getFiltered($displayers, $request, $original, $transformed, $code)) {
            return $filtered[0];
        }

        return $this->getContainer()->get($config->get('exception.default', HtmlDisplayer::class));
    }

    /**
     * Get the filtered list of displayers.
     *
     * @param \Viserio\Contracts\Exception\Displayer[] $displayers
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                               $original
     * @param \Throwable                               $transformed
     * @param int                                      $code
     *
     * @return \Viserio\Contracts\Exception\Displayer[]
     */
    protected function getFiltered(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        $container = $this->getContainer();
        $filters   = array_merge(
            $this->filters,
            $container->get(RepositoryContract::class)->get('exception.filters', [])
        );

        foreach ($filters as $filter) {
            $filterClass = is_object($filter) ? $filter : $container->get($filter);

            if (! $filterClass) {
                continue;
            }

            $displayers  = $filterClass->filter($displayers, $request, $original, $transformed, $code);
        }

        return array_values($displayers);
    }

    /**
     * Get the transformed exception.
     *
     * @param \Throwable|\Exception $exception
     *
     * @return \Throwable|\Exception
     */
    protected function getTransformed($exception)
    {
        $container    = $this->container;
        $transformers = array_merge(
            $this->transformers,
            $container->get(RepositoryContract::class)->get('exception.transformers', [])
        );

        foreach ($transformers as $transformer) {
            $transformerClass = is_object($transformer) ? $transformer : $container->get($transformer);
            $exception        = $transformerClass->transform($exception);
        }

        return $exception;
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
     * Get a prepared response with the transformed exception.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Throwable|\Exception                 $exception
     * @param \Throwable|\Exception                 $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getPreparedResponse(
        ContainerInterface $container,
        $exception,
        $transformed
    ): ResponseInterface {
        try {
            $response = $this->getResponse(
                $container->get(ServerRequestInterface::class),
                $exception,
                $transformed
            );
        } catch (Throwable | Exception $exception) {
            $this->report($exception);

            $response = $container->get(ResponseFactoryInterface::class)->createResponse();
            $response = $response->withStatus(500, HttpStatus::getReasonPhrase(500));
        }

        return $response;
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
}
