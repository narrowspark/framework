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

                $callable = $slice($this->getDelegateMiddleware($stack), $pipe);

                return $callable($passable);
            };
        };
    }

    /**
     * Private delegate callable middleware for the pipe.
     *
     * @param callable $middleware
     *
     * @return anonymous//src/Viserio/Routing/Pipeline.php@return object
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
