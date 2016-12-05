<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Middleware;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\WebProfiler\WebProfiler;

class WebProfilerMiddleware implements ServerMiddlewareInterface
{
    /**
     * Create a new middleware instance.
     *
     * @param \Viserio\WebProfiler\WebProfiler $webprofiler
     */
    public function __construct(WebProfiler $webprofiler)
    {
        $this->webprofiler = $webprofiler;
    }

    /**
     * {@inhertidoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $response = $delegate->process($request);

        // Modify the response to add the webprofiler
        return $this->webprofiler->modifyResponse($request, $response);
    }
}
