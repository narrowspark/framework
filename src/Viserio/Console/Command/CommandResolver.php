<?php
namespace Viserio\Console\Command;

use Viserio\Contracts\Container\Container as ContainerContract;

/**
 * Command.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4
 */
class CommandResolver
{
    /**
     * Container instance.
     *
     * @var \Viserio\Contracts\Container\Container
     */
    protected $container;

    /**
     * Commands.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Constructor.
     *
     * @param ContainerContract $container
     */
    public function __construct(ContainerContract $container)
    {
        $this->container = $container;
    }

    /**
     * Check if container key match '.command'.
     *
     * @return array
     */
    public function commands()
    {
        if ($this->commands === null) {
            foreach ($this->container->getKeys() as $serviceName) {
                if (preg_match('/\.command$/', $serviceName)) {
                    $this->commands[] = $this->container->get($serviceName);
                }
            }
        }

        return $this->commands;
    }
}
