<?php
namespace Viserio\Middleware;

use RuntimeException;
use Interop\Container\ContainerInterface;

class Dispatcher
{
    /**
     * All of the short-hand keys for middlewares.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * The container implementation.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Lock status of the pipeline
     *
     * @var bool
     */
    protected $locked = false;

    /**
     * Create a new class instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function pipe(callable $middleware)
    {
        // Check if the pipeline is locked
        if ($this->locked) {
            throw new RuntimeException('Middleware can’t be added once the stack is dequeuing');
        }

        $this->middleware = $middleware;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response)
    {
        // Lock the pipeline
        $this->locked = true;

        return (new Pipeline($this->container))
            ->send($request)
            ->through($this->middleware)
            ->then(function ($request, $response) {
                return $request;
            });
    }

    /**
     * @method ResponseInterface __invoke(RequestInterface $request, ResponseInterface $response)
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response)
    {
        return $this($request, $response);
    }
}
