<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Profiler\Controller;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Viserio\Component\Routing\AbstractController;
use Viserio\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Contract\Session\Store as StoreContract;

class AssetController extends AbstractController
{
    /**
     * Response factory instance.
     *
     * @var \Psr\Http\Message\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Stream factory instance.
     *
     * @var \Psr\Http\Message\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * Profiler instance.
     *
     * @var \Viserio\Contract\Profiler\Profiler
     */
    protected $profiler;

    /**
     * Create a new AssetController instance.
     */
    public function __construct(
        ServerRequestInterface $serverRequest,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ProfilerContract $profiler
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->profiler = $profiler;
        $session = $serverRequest->getAttribute('session');

        if ($session instanceof StoreContract) {
            $session->reflash();
        }
    }

    /**
     * Return the javascript for the Debugbar.
     */
    public function js(): ResponseInterface
    {
        $renderer = $this->profiler->getAssetsRenderer();

        $stream = $this->streamFactory->createStream(
            $renderer->dumpAssetsToString('js')
        );

        $response = $this->responseFactory->createResponse();
        $response = $response->withHeader('content-type', 'text/js');

        return $response->withBody($stream);
    }

    /**
     * Return the stylesheets for the Debugbar.
     */
    public function css(): ResponseInterface
    {
        $renderer = $this->profiler->getAssetsRenderer();

        $stream = $this->streamFactory->createStream(
            $renderer->dumpAssetsToString('css')
        );

        $response = $this->responseFactory->createResponse();
        $response = $response->withHeader('content-type', 'text/css');

        return $response->withBody($stream);
    }
}
