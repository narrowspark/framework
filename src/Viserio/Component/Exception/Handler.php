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
use Viserio\Component\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contract\Exception\Filter as FilterContract;
use Viserio\Component\Contract\Exception\Handler as HandlerContract;
use Viserio\Component\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
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
     * Create a new handler instance.
     *
     * @param \Psr\Container\ContainerInterface              $data
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param null|\Psr\Log\LoggerInterface                  $logger
     */
    public function __construct($data, ResponseFactoryInterface $responseFactory, ?LoggerInterface $logger = null)
    {
        parent::__construct($data, $logger);

        $this->filters    = $this->getFormattedFilters();
        $this->displayers = $this->getFormattedDisplayers();

        $this->setResponseFactory($responseFactory);
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
        $exceptionMessage = $exception->getMessage();
        $exceptionName    = get_class($exception);

        $output->writeln(sprintf(
        '<bg=red;options=bold>%s</> : <comment>%s</>',
            $exceptionName,
            $exceptionMessage
        ));
        $output->writeln('');
        $output->writeln(sprintf(
            ' at <fg=green>%s</>' . ': <fg=green>%s</>',
            $exception->getFile(),
            $exception->getLine()
        ));
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
     * @return \Viserio\Component\Contract\Exception\Displayer
     */
    protected function getDisplayer(
        ?ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): DisplayerContract {
        if ($request !== null && $filtered = $this->getFiltered($this->displayers, $request, $original, $transformed, $code)) {
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
     * @param \Viserio\Component\Contract\Exception\Displayer[] $displayers
     * @param \Psr\Http\Message\ServerRequestInterface          $request
     * @param \Throwable                                        $original
     * @param \Throwable                                        $transformed
     * @param int                                               $code
     *
     * @return \Viserio\Component\Contract\Exception\Displayer[]
     */
    protected function getFiltered(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        foreach ($this->filters as $filter) {
            $filterClass = \is_object($filter) ? $filter : $this->container->get($filter);

            $displayers  = $filterClass->filter($displayers, $request, $original, $transformed, $code);
        }

        return \array_values($displayers);
    }

    /**
     * Formant's the displayers array to a key (class name) value (object/class name).
     *
     * @return array
     */
    private function getFormattedDisplayers(): array
    {
        $displayers = [];

        foreach ($this->resolvedOptions['displayers'] as $key => $displayer) {
            if (is_numeric($key)) {
                $key = is_string($displayer) ? $displayer : \get_class($displayer);
            }

            $displayers[$key] = $displayer;
        }

        return $displayers;
    }

    /**
     * Formant's the filters array to a key (class name) value (object/class name).
     *
     * @return array
     */
    private function getFormattedFilters(): array
    {
        $filters = [];

        foreach ($this->resolvedOptions['filters'] as $key => $filter) {
            if (is_numeric($key)) {
                $key = is_string($filter) ? $filter : \get_class($filter);
            }

            $filters[$key] = $filter;
        }

        return $filters;
    }
}
