<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Controllers;

use Interop\Http\Factory\ResponseFactoryInterface;
use Interop\Http\Factory\StreamFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Routing\AbstractController;
use Viserio\Component\Profiler\Profiler;

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
     * @var \Viserio\Component\Profiler\Profiler
     */
    protected $Profiler;

    /**
     * Create a new AssetController instance.
     *
     * @param \IPsr\Http\Message\ServerRequestInterface      $serverRequest
     * @param \Interop\Http\Factory\ResponseFactoryInterface $responseFactory
     * @param \Interop\Http\Factory\StreamFactoryInterface   $streamFactory
     * @param \Viserio\Component\Profiler\Profiler     $Profiler
     */
    public function __construct(
        ServerRequestInterface $serverRequest,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory,
        Profiler $Profiler
    ) {
        $this->responseFactory = $responseFactory;
        $this->streamFactory   = $streamFactory;
        $this->Profiler     = $Profiler;

        if ($session = $serverRequest->getAttribute('session')) {
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
