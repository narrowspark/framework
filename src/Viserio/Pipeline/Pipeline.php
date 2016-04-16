<?php
namespace Viserio\Pipeline;

use Closure;
use Interop\Container\ContainerInterface;
use Viserio\Contracts\Pipeline\Pipeline as PipelineContract;
use Viserio\Support\Traits\ContainerAwareTrait;

class Pipeline implements PipelineContract
{
    use ContainerAwareTrait;

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
     * Set the object being sent through the pipeline.
     *
     * @param mixed $traveler
     *
     * @return $this
     */
    public function send($traveler)
    {
        $this->traveler = $traveler;

        return $this;
    }

    /**
     * Set the array of stages.
     *
     * @param array|mixed $stages
     *
     * @return self
     */
    public function through($stages)
    {
        $this->stages = is_array($stages) ? $stages : func_get_args();

        return $this;
    }

    /**
     * Set the method to call on the stages.
     *
     * @param string $method
     *
     * @return $this
     */
    public function via($method)
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param \Closure $destination
     *
     * @return mixed
     */
    public function then(Closure $destination)
    {
        $firstSlice = $this->getInitialSlice($destination);

        $stages = array_reverse($this->stages);

        return call_user_func(
            array_reduce(
                $stages,
                $this->getSlice(),
                $firstSlice
            ),
            $this->traveler
        );
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     *
     * @return \Closure
     */
    protected function getSlice()
    {
        return function ($stack, $stage) {
            return function ($traveler) use ($stack, $stage) {
                $parameters = [$traveler, $stack];

                if (is_array($pipe)) {
                    $pipe = array_values($pipe);
                    $parameters = array_merge($parameters, array_splice($pipe, 1));
                    list($pipe) = $pipe;
                }

                // If the $stage is an instance of a Closure, we will just call it directly.
                if ($stage instanceof Closure) {
                    return call_user_func_array($pipe, $parameters);

                // Otherwise we'll resolve the stages out of the container and call it with
                // the appropriate method and arguments, returning the results back out.
                } elseif ($this->container) {
                    list($name, $additional) = $this->parseStageString($stage);

                    if ($this->getContainer()->has($name)) {
                        $merge = array_merge($parameters, $additional);

                        return call_user_func_array(
                            [
                                $this->getContainer()->get($name),
                                $this->method,
                            ],
                            $merge
                        );
                    }
                } elseif (is_array($stage)) {
                    $reflectionClass = new ReflectionClass(array_shift($stage));

                    return call_user_func_array(
                        $reflectionClass->newInstanceArgs($pipe),
                        [$traveler, $stack]
                    );
                }

                // If the pipe is already an object we'll just make a callable and pass it to
                // the pipe as-is. There is no need to do any extra parsing and formatting
                // since the object we're given was already a fully instantiated object.
                return call_user_func_array(new $stage(), [$traveler, $stack]);
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
    protected function getInitialSlice(Closure $destination)
    {
        return function ($traveler) use ($destination) {
            return call_user_func($destination, $traveler);
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     *
     * @param string $stage
     *
     * @return array
     */
    protected function parseStageString($stage)
    {
        list($name, $parameters) = array_pad(explode(':', $stage, 2), 2, []);

        if (is_string($parameters)) {
            $parameters = explode(',', $parameters);
        }

        return [$name, $parameters];
    }
}
