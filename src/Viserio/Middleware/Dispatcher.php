<?php
namespace Viserio\Middleware;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use RuntimeException;
use SplQueue;
use Viserio\Contracts\Container\ContainerAware;

class Dispatcher
{
    /**
     * All of the short-hand keys for middlewares.
     *
     * @var SplQueue
     */
    protected $middleware;

    /**
     * Container instance.
     *
     * @var \Interop\Container\ContainerInterface|null
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
     */
    public function __construct()
    {
        $this->middleware = new SplQueue();
    }

    /**
     * Set a container.
     *
     * @param \Interop\Container\ContainerInterface $container
     *
     * @return self
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Get the container.
     *
     * @return \Interop\Container\ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
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

        if ($middleware instanceof ContainerAware || method_exists($middleware, 'setContainer')) {
            $middleware->setContainer($this->getContainer());
        }

        $this->middleware->enqueue($middleware);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Request $request, Response $response)
    {
        // Lock the pipeline
        $this->locked = true;

        // Check if the pipe-line is broken or if we are at the end of the queue
        if (!$this->middleware->isEmpty()) {
            // Pick the next middleware from the queue
            $next = $this->middleware->dequeue();
            // Call the next middleware (if callable)
            return (is_callable($next)) ? $next($request, $response, $this) : $response;
        }

        // Nothing left to do, return the response
        return $response;
    }
}
