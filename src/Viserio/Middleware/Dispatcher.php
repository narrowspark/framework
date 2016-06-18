<?php
namespace Viserio\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use SplDoublyLinkedList;
use SplStack;
use Viserio\Contracts\Middleware\Stack as StackContract;
use Viserio\Contracts\Middleware\Frame as FrameContract;
use Viserio\Contracts\Middleware\Middleware as MiddlewareContract;
use Viserio\Support\Traits\ContainerAwareTrait;

class Dispatcher implements StackContract
{
    use ContainerAwareTrait;

    /**
     * All of the short-hand keys for middlewares.
     *
     * @var \SplStack $response
     */
    protected $stack;

    /**
     * A response instance.
     *
     * @var ResponseInterface $response
     */
    protected $response;

    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;
        $this->stack = new SplStack();
        $this->stack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP);
    }

    /**
     * {@inheritdoc}
     */
    public function withMiddleware(MiddlewareContract $middleware): StackContract
    {
        $this->stack->push($this->isContainerAware($middleware));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware(MiddlewareContract $middleware): StackContract
    {
        foreach ($this->stack as $key => $stackMiddleware) {
            if (get_class($this->stack[$key]) === get_class($middleware)) {
                unset($this->stack[$key]);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function process(RequestInterface $request): ResponseInterface
    {
        return (new class($this->stack, $this->response) implements FrameContract {
            private $middlewares;

            private $response;

            private $index = 0;

            public function __construct(SplStack $stack, ResponseInterface $response)
            {
                $this->middlewares = $stack;
                $this->response = $response;
            }

            public function next(RequestInterface $request): ResponseInterface
            {
                if (!isset($this->middlewares[$this->index])) {
                    return $this->response;
                }

                return $this->middlewares[$this->index]->process($request, $this->nextFrame());
            }

            private function nextFrame()
            {
                $new = clone $this;
                ++$new->index;

                return $new;
            }
        }
        )->next($request);
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
        if (method_exists($middleware, 'setContainer')) {
            $middleware->setContainer($this->getContainer());
        }

        return $middleware;
    }
}
