<?php
namespace Viserio\Middleware;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Viserio\Contracts\Container\ContainerAware;
use Viserio\Contracts\Middleware\Dispatcher as DispatcherContract;
use Viserio\Contracts\Middleware\Factory as FactoryContract;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;
use Viserio\Support\Traits\ContainerAwareTrait;

class Dispatcher implements DispatcherContract
{
    use ContainerAwareTrait;

    /**
     * All of the short-hand keys for middlewares.
     *
     * @var array
     */
    protected $middlewares = [];

    /**
     * Create a new dispatcher instance.
     */
    public function __construct(FactoryContract $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function pipe($middleware): DispatcherContract
    {
        $this->middlewares[] = $this->normalize($middleware);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function run(ServerRequestInterface $request, callable $default): ResponseInterface
    {
        return (new class($this->middlewares, $this->factory, $default) implements FrameContract
 {
     private $middlewares;

     private $index = 0;

     private $factory;

     private $default;

     public function __construct(array $middleware, FactoryContract $factory, callable $default)
     {
         $this->middlewares = $middleware;
         $this->factory     = $factory;
         $this->default     = $default;
     }

     public function next(ServerRequestInterface $request): ResponseInterface
     {
         if (!isset($this->middlewares[$this->index])) {
             return ($this->default)($request);
         }

         return $this->middlewares[$this->index]->handle($request, $this->nextFrame());
     }

     public function factory(): FactoryContract
     {
         return $this->factory;
     }

     private function nextFrame()
     {
         $new = clone $this;
         $new->index++;

         return $new;
     }
 }
        )->next($request);
    }

    /**
     * Check if middleware is a callable or has MiddlewareContract.
     *
     * @param MiddlewareContract|callable(RequestInterface,FrameInterface):ResponseInterface $middleware
     *
     * @throws \InvalidArgumentException when adding a invalid middleware to the stack
     *
     * @return MiddlewareContract
     */
    private function normalize($middleware): MiddlewareContract
    {
        if ($middleware instanceof MiddlewareContract) {
            return $this->isContainerAware($middleware);
        } elseif (is_callable($middleware)) {
            return new class($middleware) implements MiddlewareContract
 {
     private $callback;

     public function __construct($middleware)
     {
         $this->callback = $middleware;
     }

                /**
                 *  {@inheritdoc}
                 */
                public function handle(ServerRequestInterface $request, FrameContract $frame): ResponseInterface
                {
                    return ($this->callback)($request, $frame);
                }
 };
        }

        throw new InvalidArgumentException('Invalid Middleware Detected.');
    }

    /**
     *  Check if middleware is aware of Interop\Container\ContainerInterface.
     *
     * @param MiddlewareContract $middleware
     *
     * @return MiddlewareContract
     */
    private function isContainerAware($middleware): MiddlewareContract
    {
        if ($middleware instanceof ContainerAware || method_exists($middleware, 'setContainer')) {
            $middleware->setContainer($this->getContainer());
        }

        return $middleware;
    }
}
