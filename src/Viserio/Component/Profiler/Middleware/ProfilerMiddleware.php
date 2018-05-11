<?php
declare(strict_types=1);
namespace Viserio\Component\Profiler\Middleware;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Viserio\Component\Contract\Profiler\Profiler as ProfilerContract;

class ProfilerMiddleware implements MiddlewareInterface
{
    /**
     * The Profiler instance.
     *
     * @var null|\Viserio\Component\Contract\Profiler\Profiler
     */
    protected $profiler;

    /**
     * Create a new middleware instance.
     *
     * @param \Psr\Container\ContainerInterface $container
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
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $server    = $request->getServerParams();
        $startTime = $server['request_time_float'] ?? \microtime(true);

        $response = $handler->handle($request);

        if ($this->profiler === null) {
            return $response;
        }

        $response = $response->withHeader('x-response-time', \sprintf('%2.3fms', (\microtime(true) - $startTime) * 1000));

        // Modify the response to add the Profiler
        return $this->profiler->modifyResponse($request, $response);
    }
}
