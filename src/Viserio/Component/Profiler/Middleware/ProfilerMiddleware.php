<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;

class ProfilerMiddleware implements MiddlewareInterface
{
    /**
     * The Profiler instance.
     *
     * @var \Viserio\Component\Contracts\Profiler\Profiler
     */
    protected $profiler;

    /**
     * Create a new middleware instance.
     *
     * @param \Viserio\Component\Contracts\Profiler\Profiler $profiler
     */
    public function __construct(ProfilerContract $profiler)
    {
        $this->profiler = $profiler;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $response = $delegate->process($request);

        // Modify the response to add the Profiler
        return $this->profiler->modifyResponse($request, $response);
    }
}
