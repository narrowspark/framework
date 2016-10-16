<?php
declare(strict_types=1);
namespace Viserio\Middleware;

use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\MiddlewareInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use LogicException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SplDoublyLinkedList;
use SplStack;
use Viserio\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Contracts\Middleware\Stack as StackContract;

class Dispatcher implements StackContract
{
    use ContainerAwareTrait;

    /**
     * All of the short-hand keys for middlewares.
     *
     * @var \SplStack
     */
    protected $stack;

    /**
     * A response instance.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * Create a new middleware instance.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    public function __construct(ResponseInterface $response)
    {
        $this->response = $response;

        $stack = new SplStack();
        $stack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP);

        $this->stack = $stack;
    }

    /**
     * {@inheritdoc}
     */
    public function withMiddleware($middleware): StackContract
    {
        if ($middleware instanceof MiddlewareInterface || $middleware instanceof ServerMiddlewareInterface) {
            $this->stack->push($this->isContainerAware($middleware));

            return $this;
        }

        throw new LogicException('Unsupported middleware type.');
    }

    /**
     * {@inheritdoc}
     */
    public function withoutMiddleware($middleware): StackContract
    {
        if ($middleware instanceof MiddlewareInterface || $middleware instanceof ServerMiddlewareInterface) {
            foreach ($this->stack as $key => $stackMiddleware) {
                if (get_class($this->stack[$key]) === get_class($middleware)) {
                    unset($this->stack[$key]);
                }
            }

            return $this;
        }

        throw new LogicException('Unsupported middleware type.');
    }

    /**
     * {@inheritdoc}
     */
    public function process(RequestInterface $request): ResponseInterface
    {
        return (new class($this->stack, $this->response) implements DelegateInterface {
            private $middlewares;

            private $response;

            private $index = 0;

            public function __construct(SplStack $stack, ResponseInterface $response)
            {
                $this->middlewares = $stack;
                $this->response = $response;
            }

            public function process(RequestInterface $request): ResponseInterface
            {
                if (! isset($this->middlewares[$this->index])) {
                    return $this->response;
                }

                return $this->middlewares[$this->index]->process($request, $this->nextProcess());
            }

            private function nextProcess()
            {
                $new = clone $this;
                ++$new->index;

                return $new;
            }
        }
        )->process($request);
    }

    /**
     *  Check if middleware is aware of Interop\Container\ContainerInterface.
     *
     * @param \Interop\Http\Middleware\MiddlewareInterface|\Interop\Http\Middleware\ServerMiddlewareInterface $middleware
     *
     * @return \Interop\Http\Middleware\MiddlewareInterface|\Interop\Http\Middleware\ServerMiddlewareInterface
     */
    private function isContainerAware($middleware)
    {
        if (method_exists($middleware, 'setContainer')) {
            $middleware->setContainer($this->getContainer());
        }

        return $middleware;
    }
}
