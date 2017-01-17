<?php
declare(strict_types=1);
namespace Viserio\Component\Pipeline;

use Closure;
use ReflectionClass;
use Viserio\Component\Contracts\Container\Traits\ContainerAwareTrait;
use Viserio\Component\Contracts\Pipeline\Pipeline as PipelineContract;
use Viserio\Component\Support\Traits\InvokerAwareTrait;

class Pipeline implements PipelineContract
{
    use ContainerAwareTrait;
    use InvokerAwareTrait;

    /**
     * The object being passed through the pipeline.
     *
     * @var mixed
     */
    protected $traveler;

    /**
     * The method to call on each stage.
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     * The array of class pipes.
     *
     * @var array
     */
    protected $stages = [];

    /**
     * {@inheritdoc}
     */
    public function send($traveler): PipelineContract
    {
        $this->traveler = $traveler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function through($stages): PipelineContract
    {
        $this->stages = is_array($stages) ? $stages : func_get_args();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function via(string $method): PipelineContract
    {
        $this->method = $method;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function then(Closure $destination)
    {
        $firstSlice = $this->getInitialSlice($destination);

        $stages = array_reverse($this->stages);

        $callable = array_reduce($stages, $this->getSlice(), $firstSlice);

        return $callable($this->traveler);
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function getSlice(): Closure
    {
        return function ($stack, $stage) {
            return function ($traveler) use ($stack, $stage) {
                // If the $stage is an instance of a Closure, we will just call it directly.
                if ($stage instanceof Closure) {
                    return $stage($traveler, $stack);
                // Otherwise we'll resolve the stages out of the container and call it with
                // the appropriate method and arguments, returning the results back out.
                } elseif ($this->container && ! is_object($stage)) {
                    return $this->sliceThroughContainer($traveler, $stack, $stage);
                } elseif (is_array($stage)) {
                    $reflectionClass = new ReflectionClass(array_shift($stage));
                    $parameters      = [$traveler, $stack];

                    return $reflectionClass->newInstanceArgs($stage)(...$parameters);
                }

                // If the pipe is already an object we'll just make a callable and pass it to
                // the pipe as-is. There is no need to do any extra parsing and formatting
                // since the object we're given was already a fully instantiated object.
                $parameters = [$traveler, $stack];

                return $stage->{$this->method}(...$parameters);
            };
        };
    }

    /**
     * Get the initial slice to begin the stack call.
     *
     * @param \Closure $destination
     *
     * @return \Closure
     */
    protected function getInitialSlice(Closure $destination): Closure
    {
        return function ($traveler) use ($destination) {
            return $destination($traveler);
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     *
     * @param string $stage
     *
     * @return array
     */
    protected function parseStageString(string $stage): array
    {
        list($name, $parameters) = array_pad(explode(':', $stage, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
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

        if ($this->container->has($name)) {
            $merge = array_merge([$traveler, $stack], $parameters);

            return $this->getInvoker()->call(
                [
                    $this->container->get($name),
                    $this->method,
                ],
                $merge
            );
        }
    }
}
