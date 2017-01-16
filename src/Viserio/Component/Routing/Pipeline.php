<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Closure;
use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\RequestInterface;
use Viserio\Component\Pipeline\Pipeline as BasePipeline;

class Pipeline extends BasePipeline
{
    /**
     * The method to call on each stage.
     *
     * @var string
     */
    protected $method = 'process';

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function getSlice(): Closure
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                $slice = parent::getSlice();

                $callable = $slice($this->getDelegateMiddleware($stack), $pipe);

                return $callable($passable);
            };
        };
    }

    /**
     * Resolve from container.
     *
     * @param mixed  $traveler
     * @param mixed  $stack
     * @param string $stage
     *
     * @return mixed
     */
    protected function sliceThroughContainer($traveler, $stack, string $stage)
    {
        list($name, $parameters) = $this->parseStageString($stage);
        $parameters              = array_merge([$traveler, $stack], $parameters);

        if ($this->container->has($name)) {
            return $this->getInvoker()->call(
                [
                    $this->container->get($name),
                    $this->method,
                ],
                $parameters
            );
        }

        // Check if container has a make function.
        if (method_exists($this->container, 'make')) {
            return call_user_func_array([$this->container->make($name), $this->method], $parameters);
        }
    }

    /**
     * Private delegate callable middleware for the pipe.
     *
     * @param callable $middleware
     *
     * @return object
     */
    private function getDelegateMiddleware(callable $middleware)
    {
        return new class($middleware) implements DelegateInterface {
            /**
             * @var callable
             */
            private $middleware;

            /**
             * Create a new delegate callable middleware instance.
             *
             * @param callable $middleware
             */
            public function __construct(callable $middleware)
            {
                $this->middleware = $middleware;
            }

            /**
             * {@inheritdoc}
             */
            public function process(RequestInterface $request)
            {
                return call_user_func($this->middleware, $request);
            }
        };
    }
}
