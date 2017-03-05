<?php
declare(strict_types=1);
namespace Viserio\Component\Routing;

use Closure;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use Viserio\Component\Contracts\Container\Container as ContainerContract;
use Viserio\Component\Pipeline\Pipeline as BasePipeline;

class Pipeline extends BasePipeline
{
    /**
     * {@inheritdoc}
     */
    protected $method = 'process';

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    protected function sliceThroughContainer($traveler, $stack, string $stage)
    {
        list($name, $parameters) = $this->parseStageString($stage);
        $parameters              = array_merge([$traveler, $stack], $parameters);
        $class                   = null;

        if ($this->container->has($name)) {
            $class = $this->container->get($name);
        // @codeCoverageIgnoreStart
        } elseif ($this->container instanceof ContainerContract) {
            $class = $this->container->make($name);
        } else {
            throw new RuntimeException(sprintf('Class [%s] is not being managed by the container.', $name));
        }
        // @codeCoverageIgnoreStop

        return $this->getInvoker()->call(
            [
                $class,
                $this->method,
            ],
            $parameters
        );
    }

    /**
     * Private delegate callable middleware for the pipe.
     *
     * @param callable $middleware
     *
     * @return object
     *
     * @codeCoverageIgnore
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
            public function process(ServerRequestInterface $request)
            {
                return call_user_func($this->middleware, $request);
            }
        };
    }
}
