<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Http;

use Interop\Http\Factory\ResponseFactoryInterface;
use Narrowspark\Http\Message\Util\Traits\AcceptHeaderTrait;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Throwable;
use Viserio\Component\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Component\Contract\Exception\Filter as FilterContract;
use Viserio\Component\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Component\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\Displayer\JsonApiDisplayer;
use Viserio\Component\Exception\Displayer\JsonDisplayer;
use Viserio\Component\Exception\Displayer\SymfonyDisplayer;
use Viserio\Component\Exception\Displayer\ViewDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsJsonDisplayer;
use Viserio\Component\Exception\Displayer\WhoopsPrettyDisplayer;
use Viserio\Component\Exception\ErrorHandler;
use Viserio\Component\Exception\ExceptionIdentifier;
use Viserio\Component\Exception\Filter\CanDisplayFilter;
use Viserio\Component\Exception\Filter\ContentTypeFilter;
use Viserio\Component\Exception\Filter\VerboseFilter;
use Viserio\Component\Exception\Traits\RegisterAndUnregisterTrait;

class Handler extends ErrorHandler implements HttpHandlerContract, RequiresMandatoryOptionsContract
{
    use ResponseFactoryAwareTrait;
    use AcceptHeaderTrait;
    use RegisterAndUnregisterTrait;

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
     * @param array|\ArrayAccess                             $config
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param null|\Psr\Log\LoggerInterface                  $logger
     */
    public function __construct($config, ResponseFactoryInterface $responseFactory, ?LoggerInterface $logger = null)
    {
        parent::__construct($config, $logger);

        $this->filters    = $this->transformArray($this->resolvedOptions['http']['filters']);
        $this->displayers = $this->transformArray($this->resolvedOptions['http']['displayers']);

        $this->setResponseFactory($responseFactory);
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryOptions(): array
    {
        return ['env'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDefaultOptions(): array
    {
        return \array_merge(
            parent::getDefaultOptions(),
            [
                'http' => [
                    'displayers' => [
                        WhoopsPrettyDisplayer::class,
                        SymfonyDisplayer::class,
                        ViewDisplayer::class,
                        HtmlDisplayer::class,
                        WhoopsJsonDisplayer::class,
                        JsonDisplayer::class,
                        JsonApiDisplayer::class,
                    ],
                    'default_displayer' => HtmlDisplayer::class,
                    'filters'           => [
                        VerboseFilter::class,
                        CanDisplayFilter::class,
                        ContentTypeFilter::class,
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addDisplayer(DisplayerContract $displayer): HttpHandlerContract
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
    public function addFilter(FilterContract $filter): HttpHandlerContract
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
    public function handleException(Throwable $exception): void
    {
        try {
            parent::handleException($exception);
        } catch (Throwable $transformedException) {
            $response = $this->getPreparedResponse(
                null,
                $exception,
                $transformedException
            );

            echo (string) $response->getBody();
        }
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
            $response = $response->withHeader('content-type', 'text/plain');
        }

        return $response;
    }

    /**
     * Create a response for the given exception.
     *
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     * @param \Throwable                                    $exception
     * @param \Throwable                                    $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponse(
        ?ServerRequestInterface $request,
        Throwable $exception,
        Throwable $transformed
    ): ResponseInterface {
        $id        = ExceptionIdentifier::identify($exception);
        $flattened = FlattenException::create($exception);
        $code      = $flattened->getStatusCode();
        $headers   = $flattened->getHeaders();

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
        if ($request !== null) {
            $filtered = $this->getFiltered($this->make($this->displayers), $request, $original, $transformed, $code);

            if (\count($filtered) !== 0) {
                return $this->sortedFilter($filtered, $request);
            }
        }

        $defaultDisplayer = $this->resolvedOptions['http']['default_displayer'];

        if (\is_object($defaultDisplayer) && $defaultDisplayer instanceof DisplayerContract) {
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
        /** @var FilterContract $filter */
        foreach ($this->make($this->filters) as $filter) {
            $displayers = $filter->filter($displayers, $request, $original, $transformed, $code);
        }

        return \array_values($displayers);
    }

    /**
     * Sort displayer after the first found accept header.
     *
     * @param array                                    $filtered
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Viserio\Component\Contract\Exception\Displayer
     */
    private function sortedFilter(array $filtered, ServerRequestInterface $request): DisplayerContract
    {
        $accepts = self::getHeaderValuesFromString($request->getHeaderLine('Accept'));

        foreach ($accepts as $accept) {
            foreach ($filtered as $filter) {
                if ($filter->getContentType() === $accept) {
                    return $filter;
                }
            }
        }

        return $filtered[0];
    }
}
