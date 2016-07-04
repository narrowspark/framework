<?php
namespace Viserio\Bus;

use Closure;
use Interop\Container\ContainerInterface;
use Viserio\Contracts\Bus\Dispatcher as DispatcherContract;
use Viserio\Pipeline\Pipeline;
use Viserio\Support\Traits\ContainerAwareTrait;

class Dispatcher implements DispatcherContract
{
    use ContainerAwareTrait;

    /**
     * The pipeline instance for the bus.
     *
     * @var \Viserio\Pipeline\Pipeline
     */
    protected $pipeline;

    /**
     * The pipes to send commands through before dispatching.
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * All of the command-to-handler mappings.
     *
     * @var array
     */
    protected $mappings = [];

    /**
     * The queue resolver callback.
     *
     * @var \Closure|null
     */
    protected $queueResolver;

    /**
     * The fallback mapping Closure.
     *
     * @var \Closure
     */
    protected $mapper;

    /**
     * Create a new command dispatcher instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     * @param \Closure|null                         $queueResolver
     */
    public function __construct(ContainerInterface $container, Closure $queueResolver = null)
    {
        $this->container = $container;
        $this->queueResolver = $queueResolver;

        $pipeline = new Pipeline();
        $pipeline->setContainer($container);

        $this->pipeline = $pipeline;
    }
}
