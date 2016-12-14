<?php
declare(strict_types=1);
namespace Viserio\WebProfiler\Middleware;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\WebProfiler\WebProfiler as WebProfilerContract;

class WebProfilerMiddleware implements ServerMiddlewareInterface
{
    /**
     * The webprofiler instance.
     *
     * @var \Viserio\Contracts\WebProfiler\WebProfiler
     */
    protected $webprofiler;

    /**
     * Create a new middleware instance.
     *
     * @param \Viserio\Contracts\WebProfiler\WebProfiler $webprofiler
     */
    public function __construct(WebProfilerContract $webprofiler)
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
