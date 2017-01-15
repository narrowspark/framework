<?php
declare(strict_types=1);
namespace Viserio\Exception;

use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use Interop\Http\Factory\ResponseFactoryInterface;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\Exception\FlattenException;
use Throwable;
use Viserio\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Contracts\Exception\Filter as FilterContract;
use Viserio\Contracts\Exception\Handler as HandlerContract;
use Viserio\Exception\Displayers\HtmlDisplayer;
use Viserio\Exception\Filters\CanDisplayFilter;
use Viserio\Exception\Filters\VerboseFilter;

class Handler extends ErrorHandler implements HandlerContract, RequiresMandatoryOptions
{
    /**
     * Exception filters.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Exception displayers.
     *
     * @var array
     */
    protected $displayers = [];

    /**
     * Create a new exception handler instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }

    /**
     * {@inheritdoc}
     */
    public function mandatoryOptions(): iterable
    {
        return ['default_displayer', 'env'];
    }

    /**
     * {@inheritdoc}
     */
    public function defaultOptions(): iterable
    {
        return array_merge(
            parent::defaultOptions(),
            [
                'displayers'        => [],
                'default_displayer' => HtmlDisplayer::class,
                'filters'           => [
                    VerboseFilter::class,
                    CanDisplayFilter::class,
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addDisplayer(DisplayerContract $displayer): HandlerContract
    {
        $this->displayers[get_class($displayer)] = $displayer;

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
    public function addFilter(FilterContract $filter): HandlerContract
    {
        $this->filters[get_class($filter)] = $filter;

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
    public function register()
    {
        error_reporting(E_ALL);

        // The DebugClassLoader attempts to throw more helpful exceptions
        // when a class isn't found by the registered autoloaders.
        DebugClassLoader::enable();

        $this->registerErrorHandler();

        $this->registerExceptionHandler();

        if ($this->config['env'] !== 'testing') {
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

        $response = $this->getPreparedResponse(
            $container,
            $exception,
            $transformed
        );

        echo (string) $response->getBody();
    }

    /**
     * {@inheritdoc}
     */
    public function render(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        $exception = $this->prepareException($exception);

        return $this->getPreparedResponse(
            $this->container,
            $exception,
            $this->getTransformed($exception)
        );
    }

    /**
     * Get a prepared response with the transformed exception.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Throwable                            $exception
     * @param \Throwable                            $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getPreparedResponse(
        ContainerInterface $container,
        Throwable $exception,
        Throwable $transformed
    ): ResponseInterface {
        try {
            $response = $this->getResponse(
                $container->get(ServerRequestInterface::class),
                $exception,
                $transformed
            );
        } catch (Throwable $exception) {
            $this->report($exception);

            $response = $container->get(ResponseFactoryInterface::class)->createResponse();
            $response = $response->withStatus(500, HttpStatus::getReasonPhrase(500));
        }

        return $response;
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
        $id          = $this->exceptionIdentifier->identify($exception);
        $flattened   = FlattenException::create($exception);
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
        $displayers = array_merge(
            $this->displayers,
            $this->config['displayers']
        );

        if ($filtered = $this->getFiltered($displayers, $request, $original, $transformed, $code)) {
            return $filtered[0];
        }

        if (is_object($this->config['default_displayer'])) {
            return $this->config['default_displayer'];
        }

        return $this->container->get($this->config['default_displayer']);
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
        $filters = array_merge(
            $this->filters,
            $this->config['filters']
        );

        foreach ($filters as $filter) {
            $filterClass = is_object($filter) ? $filter : $this->container->get($filter);

            $displayers  = $filterClass->filter($displayers, $request, $original, $transformed, $code);
        }

        return array_values($displayers);
    }
}
