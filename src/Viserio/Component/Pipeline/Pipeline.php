<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Pipeline;

use Closure;
use InvalidArgumentException;
use ReflectionClass;
use Viserio\Component\Support\Traits\InvokerAwareTrait;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\Pipeline\Exception\RuntimeException;
use Viserio\Contract\Pipeline\Pipeline as PipelineContract;

class Pipeline implements PipelineContract
{
    use ContainerAwareTrait;
    use InvokerAwareTrait;

    /**
     * The object being passed through the pipeline.
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
        $this->stages = \is_array($stages) ? $stages : \func_get_args();

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

        $stages = \array_reverse($this->stages);

        $callable = \array_reduce($stages, $this->getSlice(), $firstSlice);

        return $callable($this->traveler);
    }

    /**
     * Get a Closure that represents a slice of the application onion.
     */
    protected function getSlice(): Closure
    {
        return function ($stack, $stage) {
            return function ($traveler) use ($stack, $stage) {
                // If the $stage is an instance of a Closure, we will just call it directly.
                if ($stage instanceof Closure) {
                    return $stage($traveler, $stack);
                }

                // Otherwise we'll resolve the stages out of the container and call it with
                // the appropriate method and arguments, returning the results back out.
                if ($this->container && ! \is_object($stage) && \is_string($stage)) {
                    return $this->sliceThroughContainer($traveler, $stack, $stage);
                }

                if (\is_array($stage)) {
                    $parameters = [$traveler, $stack];
                    $class = \array_shift($stage);

                    if (\is_object($class) && (\is_string($class) && \class_exists($class))) {
                        throw new InvalidArgumentException(\sprintf('The first entry in the array must be a class, [%s] given.', \is_object($class) ? \get_class($class) : \gettype($class)));
                    }

                    /** @var Closure $object */
                    $object = (new ReflectionClass($class))->newInstanceArgs($stage);

                    return $object(...$parameters);
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
     */
    protected function getInitialSlice(Closure $destination): Closure
    {
        return static function ($traveler) use ($destination) {
            return $destination($traveler);
        };
    }

    /**
     * Parse full pipe string to get name and parameters.
     */
    protected function parseStageString(string $stage): array
    {
        [$name, $parameters] = \array_pad(\explode(':', $stage, 2), 2, []);

        if (\is_string($parameters)) {
            $parameters = \explode(',', $parameters);
        }

        return [$name, $parameters];
    }

    /**
     * Resolve from container.
     *
     * @throws \Viserio\Contract\Pipeline\Exception\RuntimeException
     */
    protected function sliceThroughContainer($traveler, $stack, string $stage)
    {
        [$name, $parameters] = $this->parseStageString($stage);
        $parameters = \array_merge([$traveler, $stack], $parameters);

        $class = null;

        if ($this->container->has($name)) {
            $class = $this->container->get($name);
        } else {
            throw new RuntimeException(\sprintf('Class [%s] is not being managed by the container.', $name));
        }

        return $this->getInvoker()->call([$class, $this->method], $parameters);
    }
}
