<?php
namespace Viserio\Exception;

use ErrorException;
use Exception;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;
use Symfony\Component\Console\{
    Application as ConsoleApplication,
    Output\ConsoleOutput
};
use Viserio\Contracts\{
    Config\Manager as ConfigManagerContract,
    Exception\Displayer as DisplayerContract,
    Exception\Filter as FilterContract,
    Exception\Transformer as TransformerContract,
    Exception\Exception\FatalThrowableError,
    Exception\Exception\FlattenException,
    Exception\Handler as HandlerContract
};
use Viserio\Http\{
    Response,
    ServerRequestFactory
};

class Handler implements HandlerContract
{
    /**
     * The log instance.
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $log;

    /**
     * Exception displayers.
     *
     * @var array
     */
    protected $displayers = [];

    /**
     * ExceptionIdentifier instance.
     *
     * @var \Viserio\Exception\ExceptionIdentifier
     */
    protected $eIdentifier;

    /**
     * Exception levels.
     *
     * @var array
     */
    protected $defaultLevels = [
        FatalThrowableError::class => 'critical',
        Throwable::class => 'error',
    ];

    /**
     * Exception transformers.
     *
     * @var array
     */
    protected $transformers = [];

    /**
     * Exception filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * The default displayer.
     *
     * @var \Viserio\Contracts\Exception\Displayer
     */
    protected $defaultDisplayer;

    /**
     * The config instance.
     *
     * @var \Viserio\Contracts\Config\Manager
     */
    protected $config;


    /**
     * Create a new exception handler instance.
     *
     * @param \Viserio\Contracts\Config\Manager      $config
     * @param \Psr\Log\LoggerInterface               $log
     * @param Viserio\\Exception\ExceptionIdentifier $eIdentifier
     */
    public function __construct(ConfigManagerContract $config, LoggerInterface $log, ExceptionIdentifier $eIdentifier)
    {
        $this->config = $config;
        $this->log = $log;
        $this->eIdentifier = $eIdentifier;
    }

    /**
     * Set a config manager.
     *
     * @param \Viserio\Contracts\Config\Manager $config
     *
     * @return $this
     */
    public function setConfig(ConfigManagerContract $config): Manager
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config.
     *
     * @return \Viserio\Contracts\Config\Manager
     */
    public function getConfig(): ConfigManagerContract
    {
        return $this->config;
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
        if (in_array($transformer, $this->transformers)) {
            $pos = array_search($transformer, $this->transformers);

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
        if (in_array($filter, $this->filters)) {
            $pos = array_search($filter, $this->filters);

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
        $id = $this->eIdentifier->identify($exception);

        $this->log->{$level}($exception, ['identification' => ['id' => $id]]);
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function register()
    {
        error_reporting(E_ALL);

        // Register the PHP error handler.
        set_error_handler([$this, 'handleError']);

        // Register the PHP exception handler.
        set_exception_handler([$this, 'handleUncaughtException']);

        // Register the PHP shutdown handler.
        register_shutdown_function([$this, 'handleShutdown']);

        if ($this->config->get('exception::env', null) !== 'testing') {
            ini_set('display_errors', 'Off');
        }
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
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
    ) {
        if ($level & error_reporting()) {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function handleException(Throwable $exception)
    {
        if ($exception instanceof Exception) {
            $exception = new FatalThrowableError($exception);
        }

        $this->report($exception);

        $transformed = $this->getTransformed($exception);

        if (php_sapi_name() === 'cli') {
            (new ConsoleApplication())->renderException($transformed, new ConsoleOutput);
        } else {
            $request = new ServerRequestFactory();

            try {
                $response = $this->getResponse($request->createServerRequestFromGlobals(), $exception, $transformed);

                return (string) $response->getBody();
            } catch (Throwable $error) {
                $this->report($error);

                $response = new Response(500, [], HttpStatus::getReasonPhrase(500));

                return (string) $response->getBody();
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
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
                    new ErrorException(
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
     * Determine if the exception is in the "do not report" list.
     *
     * @param \Throwable $exception
     *
     * @return bool
     */
    protected function shouldntReport(Throwable $exception): bool
    {
        $dontReport = array_merge($this->dontReport, $this->config->get('', []));

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
        $levels = array_merge($this->defaultLevels, $this->config->get('exception::levels', []));

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
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Throwable                         $transformed
     * @param \Throwable                         $exception
     *
     * @return ResponseInterface
     */
    protected function getResponse(
        RequestInterface $request,
        Throwable $exception,
        Throwable $transformed
    ): ResponseInterface {
        $id = $this->eIdentifier->identify($exception);
        $flattened = FlattenException::create($transformed);
        $code = $flattened->getStatusCode();
        $headers = $flattened->getHeaders();

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
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Throwable                         $original
     * @param \Throwable                         $transformed
     * @param int                                $code
     *
     * @return \Viserio\Contracts\Exception\Displayer
     */

    protected function getDisplayer(
        RequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): DisplayerContract {
        $displayers = array_merge($this->displayers, $this->config->get('exception::displayers', []));

        if ($filtered = $this->getFiltered($displayers, $request, $original, $transformed, $code)) {
            return $filtered[0];
        }

        return $this->defaultDisplayer;
    }

    /**
     * Get the filtered list of displayers.
     *
     * @param \Viserio\Contracts\Exception\Displayer[] $displayers
     * @param \Psr\Http\Message\RequestInterface       $request
     * @param \Throwable                               $original
     * @param \Throwable                               $transformed
     * @param int                                      $code
     *
     * @return \Viserio\Contracts\Exception\Displayer[]
     */
    protected function getFiltered(
        array $displayers,
        RequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        $filters = array_merge($this->filters, $this->config->get('exception::filters', []));

        foreach ($filters as $filter) {
            $displayers = $filter->filter($displayers, $request, $original, $transformed, $code);
        }

        return array_values($displayers);
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
        $transformers = array_merge($this->transformers, $this->config->get('exception::transformers', []));

        foreach ($transformers as $transformer) {
            $exception = $transformer->transform($exception);
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
}
