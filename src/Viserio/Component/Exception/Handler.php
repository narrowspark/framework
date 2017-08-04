<?php
declare(strict_types=1);
namespace Viserio\Component\Exception;

use Exception;
use Interop\Http\Factory\ResponseFactoryInterface;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Debug\DebugClassLoader;
use Symfony\Component\Debug\Exception\FlattenException;
use Throwable;
use Viserio\Component\Console\Application as ConsoleApplication;
use Viserio\Component\Contracts\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contracts\Exception\Filter as FilterContract;
use Viserio\Component\Contracts\Exception\Handler as HandlerContract;
use Viserio\Component\Contracts\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Filter\CanDisplayFilter;
use Viserio\Component\Exception\Filter\VerboseFilter;

class Handler extends ErrorHandler implements HandlerContract, RequiresMandatoryOptionsContract
{
    use ResponseFactoryAwareTrait;

    /**
     * Exception filters.
     *
     * @var array
     */
    private $filters = [];

    /**
     * Exception displayers.
     *
     * @var array
     */
    private $displayers = [];

    /**
     * @var null|\Viserio\Component\Console\Application
     */
    private $console;

    /**
     * Create a new handler instance.
     *
     * @param \Psr\Container\ContainerInterface              $data
     * @param \Psr\Log\LoggerInterface                       $logger
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     */
    public function __construct($data, ResponseFactoryInterface $responseFactory, LoggerInterface $logger)
    {
        parent::__construct($data, $logger);

        $this->setResponseFactory($responseFactory);
    }

    /**
     * Set a console application instance.
     *
     * @param \Viserio\Component\Console\Application $console
     *
     * @return $this
     */
    public function setConsole(ConsoleApplication $console): self
    {
        $this->console = $console;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): iterable
    {
        return ['env'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): iterable
    {
        return \array_merge(
            parent::getDefaultOptions(),
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
        $this->displayers[\get_class($displayer)] = $displayer;

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
        $this->filters[\get_class($filter)] = $filter;

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
    public function register(): void
    {
        \error_reporting(E_ALL);

        // The DebugClassLoader attempts to throw more helpful exceptions
        // when a class isn't found by the registered autoloaders.
        DebugClassLoader::enable();

        $this->registerErrorHandler();

        $this->registerExceptionHandler();

        if ($this->resolvedOptions['env'] !== 'testing') {
            $this->registerShutdownHandler();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function unregister(): void
    {
        \restore_error_handler();
    }

    /**
     * {@inheritdoc}
     */
    public function handleException(Throwable $exception): void
    {
        $exception = $this->prepareException($exception);

        try {
            $this->report($exception);
        } catch (Throwable $exception) {
            // If handler can't report exception just render it
        }

        $transformed = $this->getTransformed($exception);

        if (PHP_SAPI === 'cli') {
            if (($console = $this->getConsole()) !== null) {
                $console->renderException($transformed, new ConsoleOutput());
            } else {
                throw $transformed;
            }
        }

        $response = $this->getPreparedResponse(
            null,
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
            $request,
            $exception,
            $this->getTransformed($exception)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function renderForConsole(OutputInterface $output, Throwable $exception): void
    {
        if (($console = $this->getConsole()) !== null) {
            $console->renderException($exception, $output);

            return;
        }
    }

    /**
     * Get a prepared response with the transformed exception.
     *
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                                    $exception
     * @param \Throwable                                    $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getPreparedResponse(
        ?ServerRequestInterface $request,
        Throwable $exception,
        Throwable $transformed
    ): ResponseInterface {
        try {
            $response = $this->getResponse(
                $request,
                $exception,
                $transformed
            );
        } catch (Throwable $exception) {
            $this->report($exception);

            $response = $this->responseFactory->createResponse();
            $response = $response->withStatus(500, HttpStatus::getReasonPhrase(500));
            $response = $response->withHeader('Content-Type', 'text/plain');
        }

        return $response;
    }

    /**
     * Create a response for the given exception.
     *
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     * @param \Exception                                    $exception
     * @param \Throwable                                    $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponse(
        ?ServerRequestInterface $request,
        Exception $exception,
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
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                                    $original
     * @param \Throwable                                    $transformed
     * @param int                                           $code
     *
     * @return \Viserio\Component\Contracts\Exception\Displayer
     */
    protected function getDisplayer(
        ?ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): DisplayerContract {
        $displayers = \array_merge(
            $this->displayers,
            $this->resolvedOptions['displayers']
        );

        if ($request !== null && $filtered = $this->getFiltered($displayers, $request, $original, $transformed, $code)) {
            return $filtered[0];
        }

        $defaultDisplayer = $this->resolvedOptions['default_displayer'];

        if (\is_object($defaultDisplayer)) {
            return $defaultDisplayer;
        }

        return $this->container->get($defaultDisplayer);
    }

    /**
     * Get the filtered list of displayers.
     *
     * @param \Viserio\Component\Contracts\Exception\Displayer[] $displayers
     * @param \Psr\Http\Message\ServerRequestInterface           $request
     * @param \Throwable                                         $original
     * @param \Throwable                                         $transformed
     * @param int                                                $code
     *
     * @return \Viserio\Component\Contracts\Exception\Displayer[]
     */
    protected function getFiltered(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        $filters = \array_merge(
            $this->filters,
            $this->resolvedOptions['filters']
        );

        foreach ($filters as $filter) {
            $filterClass = \is_object($filter) ? $filter : $this->container->get($filter);

            $displayers  = $filterClass->filter($displayers, $request, $original, $transformed, $code);
        }

        return \array_values($displayers);
    }

    /**
     * Get a console instance form container or take the given instance.
     *
     * @return null|\Viserio\Component\Console\Application
     */
    private function getConsole(): ? ConsoleApplication
    {
        if ($this->console === null && $this->container !== null && $this->container->has(ConsoleApplication::class)) {
            return $this->console = $this->container->get(ConsoleApplication::class);
        }

        return $this->console;
    }
}
