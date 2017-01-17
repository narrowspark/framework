<?php
declare(strict_types=1);
namespace Viserio\Component\WebProfiler\Middleware;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\WebProfiler\WebProfiler as WebProfilerContract;

class WebProfilerMiddleware implements ServerMiddlewareInterface
{
    /**
     * The webprofiler instance.
     *
     * @var \Viserio\Component\Contracts\WebProfiler\WebProfiler
     */
    protected $webprofiler;

    /**
     * Create a new middleware instance.
     *
     * @param \Viserio\Component\Contracts\WebProfiler\WebProfiler $webprofiler
     */
    public function __construct(WebProfilerContract $webprofiler)
    {
        $this->webprofiler = $webprofiler;
    }

    /**
     * {@inhertidoc}.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface      $delegate
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $response = $delegate->process($request);

        // Modify the response to add the webprofiler
        return $this->webprofiler->modifyResponse($request, $response);
    }
}
