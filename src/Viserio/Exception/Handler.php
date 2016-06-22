<?php
namespace Viserio\Exception;

use ErrorException;
use Throwable;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\Console\{
    Application as ConsoleApplication,
    Output\ConsoleOutput
};
use Viserio\Config\Traits\ConfigAwareTrait;
use Viserio\Contracts\{
    Config\Manager as ConfigManagerContract,
    Exception\Displayer as DisplayerContract,
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
    use ConfigAwareTrait;

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
        'Symfony\Component\HttpKernel\Exception\HttpExceptionInterface' => 'warning',
        'Symfony\Component\Debug\Exception\FatalThrowableError' => 'critical',
        'Throwable' => 'error',
    ];

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
     * Create a new exception handler instance.
     *
     * @param Viserio\Contracts\Config\Manager $config
     * @param \Psr\Log\LoggerInterface         $log
     * @param bool                             $debug
     */
    public function __construct(ConfigManagerContract $config, LoggerInterface $log)
    {
        $this->config = $config;
        $this->eIdentifier = new ExceptionIdentifier();
        $this->log = $log;
    }

    /**
     * {@inheritdoc}
     */
    public function addDisplayer(DisplayerContract $displayer): HandlerContract
    {
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
        $this->transformers[] = $transformer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTransformers(): array
    {
        return  $this->transformers;
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
     */
    public function shouldReport(Throwable $exception): bool
    {
        return ! $this->shouldntReport($exception);
    }

    /**
     * {@inheritdoc}
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
        $exception = new FatalThrowableError($exception);

        $this->report($exception);

        $transformed = $this->getTransformed($exception);
        $request = new ServerRequestFactory();

        if (php_sapi_name() === 'cli') {
            $console = new ConsoleApplication();
            $console->renderException(new ConsoleOutput, $exception);
        } else {
            try {
                $respone = $this->getResponse($request->createServerRequestFromGlobals(), $exception, $transformed);

                return $response->body();
            } catch (Throwable $error) {
                $this->report($error);

                $respone = new Response(500, [], HttpStatus::getReasonPhrase(500));

                return $response->body();
            }
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
        foreach ($this->dontReport as $type) {
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
     * @return Psr\Http\Message\ResponseInterface
     */
    protected function getResponse(
        RequestInterface $request,
        Throwable $exception,
        Throwable $transformed
    ): ResponseInterface {
        $flattened = FlattenException::create($transformed);
        $code = $flattened->getStatusCode();
        $headers = $flattened->getHeaders();

        return $this->getDisplayer($request, $exception, $transformed, $code)->display($transformed, $id, $code, $headers);
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
        $transformers = array_merge($this->transformers, $this->config-get('exception::transformers', []));

        foreach ($transformers as $transformer) {
            $exception = $transformer->transform($exception);
        }

        return $exception;
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
     * Determine if the error type is fatal.
     *
     * @param int $type
     *
     * @return bool
     */
    protected function isFatal(int $type): bool
    {
        return in_array($type, [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE], true);
    }
}
