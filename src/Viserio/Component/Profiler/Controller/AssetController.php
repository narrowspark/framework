<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Controller;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;
use Viserio\Component\Contract\Session\Store as StoreContract;
use Viserio\Component\Routing\AbstractController;

class AssetController extends AbstractController
{
    /**
     * Response factory instance.
     *
     * @var \Interop\Http\Factory\ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * Stream factory instance.
     *
     * @var \Interop\Http\Factory\StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * Profiler instance.
     *
     * @var \Viserio\Component\Contract\Profiler\Profiler
     */
    protected $profiler;

    /**
     * Create a new AssetController instance.
     *
     * @param \Psr\Http\Message\ServerRequestInterface       $serverRequest
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param \Interop\Http\Factory\StreamFactoryInterface   $streamFactory
     * @param \Viserio\Component\Contract\Profiler\Profiler  $profiler
     */
    public function __construct(
        ServerRequestInterface $serverRequest,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        ProfilerContract $profiler
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->profiler        = $profiler;
        $session               = $serverRequest->getAttribute('session');

        if ($session instanceof StoreContract) {
            $session->reflash();
        }
    }

    /**
     * Return the javascript for the Debugbar.
     *
     * @return \Psr\Http\Message\ResponseInterface
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
     *
     * @return \Psr\Http\Message\ResponseInterface
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
