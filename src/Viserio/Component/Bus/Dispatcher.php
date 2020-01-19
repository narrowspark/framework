<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\Bus;

use Closure;
use Psr\Container\ContainerInterface;
use Viserio\Component\Pipeline\Pipeline;
use Viserio\Component\Support\Traits\InvokerAwareTrait;
use Viserio\Contract\Bus\Dispatcher as DispatcherContract;
use Viserio\Contract\Bus\Exception\InvalidArgumentException;
use Viserio\Contract\Container\Traits\ContainerAwareTrait;
use Viserio\Contract\Pipeline\Pipeline as PipelineContract;

class Dispatcher implements DispatcherContract
{
    use ContainerAwareTrait;
    use InvokerAwareTrait;

    protected PipelineContract $pipeline;

    /**
     * The pipes to send commands through before dispatching.
     *
     * @var array
     */
    protected array $pipes = [];

    /**
     * All of the command-to-handler mappings.
     *
     * @var array
     */
    protected array $mappings = [];

    /**
     * The method to call on handler.
     *
     * @var string
     */
    protected string $method = 'handle';

    /**
     * The fallback mapping Closure.
     *
     * @var null|Closure
     */
    protected $mapper;

    /**
     * Create a new command dispatcher instance.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        $pipeline = new Pipeline();
        $pipeline->setContainer($container);

        $this->pipeline = $pipeline;
    }

    /**
     * {@inheritdoc}
     */
    public function via(string $method): DispatcherContract
    {
        $this->method = $method;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function resolveHandler($command): object
    {
        if (\method_exists($command, $this->method)) {
            return $command;
        }

        return $this->container->get($this->getHandlerClass($command));
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlerClass($command): string
    {
        if (\method_exists($command, $this->method)) {
            return \get_class($command);
        }

        return $this->inflectSegment($command, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getHandlerMethod($command): string
    {
        if (\method_exists($command, $this->method)) {
            return $this->method;
        }

        return $this->inflectSegment($command, 1);
    }

    /**
     * {@inheritdoc}
     */
    public function maps(array $commands): void
    {
        $this->mappings = \array_merge($this->mappings, $commands);
    }

    /**
     * {@inheritdoc}
     */
    public function mapUsing(Closure $mapper): void
    {
        $this->mapper = $mapper;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch($command, ?Closure $afterResolving = null)
    {
        return $this->pipeline->send($command)->through($this->pipes)->then(function ($command) use ($afterResolving) {
            if (\method_exists($command, $this->method)) {
                return $this->getInvoker()->call([$command, $this->method]);
            }

            $handler = $this->resolveHandler($command);

            if ($afterResolving !== null) {
                $afterResolving($handler);
            }

            return $handler->{$this->getHandlerMethod($command)}($command);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function pipeThrough(array $pipes): DispatcherContract
    {
        $this->pipes = $pipes;

        return $this;
    }

    /**
     * Get the given handler segment for the given command.
     *
     * @param object $command
     * @param int    $segment
     *
     * @throws \Viserio\Contract\Bus\Exception\InvalidArgumentException
     *
     * @return string
     */
    protected function inflectSegment(object $command, int $segment): string
    {
        $className = \get_class($command);

        // Get the given segment from a given class handler.
        if (isset($this->mappings[$className])) {
            return \explode('@', $this->mappings[$className])[$segment];
        }

        // Get the given segment from a given class handler using the custom mapper.
        if (\is_callable($this->mapper)) {
            return \explode('@', \call_user_func($this->mapper, [$command]))[$segment];
        }

        throw new InvalidArgumentException(\sprintf('No handler registered for command [%s].', $className));
    }
}
