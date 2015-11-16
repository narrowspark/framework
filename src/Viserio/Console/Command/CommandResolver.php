<?php
namespace Viserio\Console\Command;

/**
 * Narrowspark - a PHP 5 framework.
 *
 * @author      Daniel Bannert <info@anolilab.de>
 * @copyright   2015 Daniel Bannert
 *
 * @link        http://www.narrowspark.de
 *
 * @license     http://www.narrowspark.com/license
 *
 * @version     0.10.0-dev
 */

use Viserio\Contracts\Container\Container as ContainerContract;

/**
 * Command.
 *
 * @author  Daniel Bannert
 *
 * @since   0.9.4-dev
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
