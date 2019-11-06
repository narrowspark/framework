<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Exception\Http;

use ArrayAccess;
use Narrowspark\Http\Message\Util\Traits\AcceptHeaderTrait;
use Narrowspark\HttpStatus\HttpStatus;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Debug\Exception\FlattenException;
use Throwable;
use Viserio\Component\Exception\Displayer\HtmlDisplayer;
use Viserio\Component\Exception\ErrorHandler;
use Viserio\Component\Exception\ExceptionIdentifier;
use Viserio\Component\Exception\Traits\RegisterAndUnregisterTrait;
use Viserio\Contract\Exception\Displayer as DisplayerContract;
use Viserio\Contract\Exception\Filter as FilterContract;
use Viserio\Contract\Exception\HttpHandler as HttpHandlerContract;
use Viserio\Contract\HttpFactory\Traits\ResponseFactoryAwareTrait;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;

class Handler extends ErrorHandler implements HttpHandlerContract, RequiresMandatoryOptionContract
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
     * @param array|ArrayAccess                          $config
     * @param \Psr\Http\Message\ResponseFactoryInterface $responseFactory
     * @param null|\Psr\Log\LoggerInterface              $logger
     */
    public function __construct($config, ResponseFactoryInterface $responseFactory, ?LoggerInterface $logger = null)
    {
        parent::__construct($config, $logger);

        $this->setResponseFactory($responseFactory);
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
    public function getDisplayers(): array
    {
        return $this->displayers;
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
                    'displayer' => [
                        'default' => HtmlDisplayer::class,
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function addDisplayer(DisplayerContract $displayer, int $priority = 0): HttpHandlerContract
    {
        $this->displayers[$priority][\get_class($displayer)] = $displayer;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addFilter(FilterContract $filter, int $priority = 0): HttpHandlerContract
    {
        $this->filters[$priority][\get_class($filter)] = $filter;

        return $this;
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
     * @param Throwable                                     $exception
     * @param Throwable                                     $transformed
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
     * @param Throwable                                     $exception
     * @param Throwable                                     $transformed
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function getResponse(
        ?ServerRequestInterface $request,
        Throwable $exception,
        Throwable $transformed
    ): ResponseInterface {
        $id = ExceptionIdentifier::identify($exception);
        $flattened = FlattenException::create($exception);
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
     * @param null|\Psr\Http\Message\ServerRequestInterface $request
     * @param Throwable                                     $original
     * @param Throwable                                     $transformed
     * @param int                                           $code
     *
     * @return \Viserio\Contract\Exception\Displayer
     */
    protected function getDisplayer(
        ?ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): DisplayerContract {
        $sortedDisplayers = [];

        \ksort($this->displayers);

        \array_walk_recursive($this->displayers, static function ($displayers, $key) use (&$sortedDisplayers): void {
            $sortedDisplayers[$key] = $displayers;
        });

        if ($request !== null) {
            $filtered = $this->getFiltered($sortedDisplayers, $request, $original, $transformed, $code);

            if (\count($filtered) !== 0) {
                return $this->sortedFilter($filtered, $request);
            }
        }

        return $sortedDisplayers[$this->resolvedOptions['http']['displayer']['default']];
    }

    /**
     * Get the filtered list of displayers.
     *
     * @param \Viserio\Contract\Exception\Displayer[]  $displayers
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param Throwable                                $original
     * @param Throwable                                $transformed
     * @param int                                      $code
     *
     * @return \Viserio\Contract\Exception\Displayer[]
     */
    protected function getFiltered(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        /** @var \Viserio\Contract\Exception\Filter[] $sortedFilters */
        $sortedFilters = [];

        \ksort($this->filters);

        \array_walk_recursive($this->filters, static function ($filter, $key) use (&$sortedFilters): void {
            $sortedFilters[$key] = $filter;
        });

        foreach ($sortedFilters as $filter) {
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
     * @return \Viserio\Contract\Exception\Displayer
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
