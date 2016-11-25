<?php
declare(strict_types=1);
namespace Viserio\Routing;

use Closure;
use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\RequestInterface;
use Viserio\Pipeline\Pipeline as BasePipeline;

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

                $callable = $slice($this->getCallableMiddleware($stack), $pipe);

                return $callable($passable);
            };
        };
    }

    /**
     * Private callable middleware for the pipe.
     *
     * @param callable $middleware
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    private function getCallableMiddleware(callable $middleware): RequestInterface
    {
        return (new class ($middleware) implements DelegateInterface {
            /**
             * @var callable
             */
            private $middleware;

            /**
             * Create a new callable middleware instance.
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
        });
    }
}
