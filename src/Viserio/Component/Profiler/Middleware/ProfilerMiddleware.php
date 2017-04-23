<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Middleware;

use Interop\Container\ContainerInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Component\Contracts\Profiler\Profiler as ProfilerContract;
use Interop\Container\ContainerInterface;

class ProfilerMiddleware implements MiddlewareInterface
{
    /**
     * The Profiler instance.
     *
     * @var \Viserio\Component\Contracts\Profiler\Profiler|null
     */
    protected $profiler;

    /**
     * Create a new middleware instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $profiler = null;

        if ($container->has(ProfilerContract::class)) {
            $profiler = $container->get(ProfilerContract::class);
        }

        $this->profiler = $profiler;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $response = $delegate->process($request);

        if ($this->profiler === null) {
            return $response;
        }

        // Modify the response to add the Profiler
        return $this->profiler->modifyResponse($request, $response);
    }
}
