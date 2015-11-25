<?php
namespace Viserio\Pipeline;

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
 * @version     0.10.0
 */

use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Viserio\Contracts\Pipeline\Stage as StageContract;
use Viserio\Contracts\Pipeline\Hub as HubContract;

/**
 * Pipeline.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
class Hub implements HubContract
{
    /**
     * The container implementation.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * All of the available pipelines.
     *
     * @var array
     */
    protected $pipelines = [];

    /**
     * Create a new Depot instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInteropInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Define the default named pipeline.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function defaults(Closure $callback)
    {
        return $this->pipeline('default', $callback);
    }

    /**
     * Define a new named pipeline.
     *
     * @param string   $name
     * @param \Closure $callback
     *
     * @return void
     */
    public function pipeline($name, Closure $callback)
    {
        $this->pipelines[$name] = $callback;
    }

    /**
     * Send an object through one of the available pipelines.
     *
     * @param mixed       $object
     * @param string|null $pipeline
     *
     * @return mixed
     */
    public function pipe($object, $pipeline = null)
    {
        $pipeline = $pipeline ?: 'default';

        return call_user_func(
            $this->pipelines[$pipeline],
            new Pipeline($this->container),
            $object
        );
    }
}
