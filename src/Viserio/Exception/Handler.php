<?php
declare(strict_types=1);
namespace Viserio\Exception;

use ErrorException;
use Interop\Container\ContainerInterface;
use Narrowspark\HttpStatus\Exception\AbstractClientErrorException;
use Narrowspark\HttpStatus\Exception\AbstractServerErrorException;
use Narrowspark\HttpStatus\Exception\NotFoundException;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\Debug\Exception\FatalThrowableError;
use Symfony\Component\Debug\Exception\FlattenException;
use Throwable;
use Viserio\Contracts\Config\Manager as ConfigManagerContract;
use Viserio\Contracts\Config\Traits\ConfigAwareTrait;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Contracts\Exception\Filter as FilterContract;
use Viserio\Contracts\Exception\Handler as HandlerContract;
use Viserio\Contracts\Exception\Transformer as TransformerContract;
use Viserio\Exception\Displayers\HtmlDisplayer;
use Viserio\Exception\Filters\CanDisplayFilter;
use Viserio\Exception\Filters\VerboseFilter;
use Viserio\Exception\Transformers\CommandLineTransformer;

class Handler implements HandlerContract
{
    use ConfigAwareTrait;
    use ContainerAwareTrait;

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
        FatalThrowableError::class => 'critical',
        Throwable::class => 'error',
        NotFoundException::class => 'notice',
        AbstractClientErrorException::class => 'notice',
        AbstractServerErrorException::class => 'error',
        Exception::class => 'error',
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
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * Create a new exception handler instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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

        if ($this->getContainer()->has(LoggerInterface::class)) {
            try {
                $logger = $this->getContainer()->get(LoggerInterface::class);
            } catch (Throwable $exception) {
                throw $exception;
            }
        }

        $level = $this->getLevel($exception);
        $id = $this->getContainer()->get(ExceptionIdentifier::class)->identify($exception);

        if ($this->getContainer()->has(LoggerInterface::class)) {
            $logger->{$level}($exception, ['identification' => ['id' => $id]]);
        }
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
        set_exception_handler([$this, 'handleException']);

        // Register the PHP shutdown handler.
        register_shutdown_function([$this, 'handleShutdown']);

        if ($this->getContainer()->get(ConfigManagerContract::class)->get('exception.env', null) !== 'testing') {
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
        $exception = new FatalThrowableError($exception);

        $this->report($exception);

        $transformed = $this->getTransformed($exception);

        if (php_sapi_name() === 'cli') {
            (new ConsoleApplication())->renderException($transformed, new ConsoleOutput());
        } else {
            $container = $this->getContainer();

            try {
                $response = $this->getResponse(
                    $container->get(ServerRequestInterface::class),
                    $exception,
                    $transformed
                );

                return (string) $response->getBody();
            } catch (Throwable $error) {
                $this->report($error);

                $response = $container->get(ResponseInterface::class);
                $response = $response->withStatus(500, HttpStatus::getReasonPhrase(500));

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
     * Render an exception into a response.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Exception                               $exception
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $transformed = $this->getTransformed($exception);

        try {
            $response = $this->getResponse(
                $this->getContainer()->get(ServerRequestInterface::class),
                $exception,
                $transformed
            );

            return $response;
        } catch (Throwable $error) {
            $this->report($error);

            $response = $this->getContainer()->get(ResponseInterface::class);
            $response = $response->withStatus(500, HttpStatus::getReasonPhrase(500));

            return $response;
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
        $dontReport = array_merge($this->dontReport, $this->getContainer()->get(ConfigManagerContract::class)->get('', []));

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
            $this->getContainer()->get(ConfigManagerContract::class)->get('exception.levels', [])
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
     * @param \Throwable                               $exception
     * @param \Throwable                               $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponse(
        ServerRequestInterface $request,
        Throwable $exception,
        Throwable $transformed
    ): ResponseInterface {
        $id = $this->getContainer()->get(ExceptionIdentifier::class)->identify($exception);
        $flattened = FlattenException::create($transformed);
        $code = $flattened->getCode();
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
        $config = $this->getContainer()->get(ConfigManagerContract::class);

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
        $filters = array_merge(
            $this->filters,
            $container->get(ConfigManagerContract::class)->get('exception.filters', [])
        );

        foreach ($filters as $filter) {
            $filterClass = is_object($filter) ? $filter : $container->get($filter);
            $displayers = $filterClass->filter($displayers, $request, $original, $transformed, $code);
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
        $container =  $this->getContainer();
        $transformers = array_merge(
            $this->transformers,
            $container->get(ConfigManagerContract::class)->get('exception.transformers', [])
        );

        foreach ($transformers as $transformer) {
            $transformerClass = is_object($transformer) ? $transformer : $container->get($transformer);
            $exception = $transformerClass->transform($exception);
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
