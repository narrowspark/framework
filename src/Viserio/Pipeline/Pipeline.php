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
 * @version     0.10.0-dev
 */

use Closure;
use Interop\Container\ContainerInterface as ContainerInteropInterface;
use Viserio\Contracts\Pipeline\Pipeline as PipelineContract;

/**
 * Pipeline.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10-dev
 */
class Pipeline implements PipelineContract
{
    /**
      * The last Stage in the pipeline.
      *
      * @var null|object
      */
    protected $lastStage = null;

    /**
      * The last Stage that was executed.
      *
      * @var null|object
      */
    protected $current = null;

    /**
      * Did all the Stages run and succeded
      *
      * @var bool
      */
    protected $ended = false;

    /**
     * The array of class pipes.
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * The method to call on each pipe.
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     * The container implementation.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $container;

    /**
     * Create a new class instance.
     *
     * @param \Interop\Container\ContainerInterface $container
     */
    public function __construct(ContainerInteropInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Create a new pipeline with an appended stage.
     *
     * @param callable $operation
     *
     * @return static
     */
    public function pipe(callable $operation)
    {

    }

    /**
     * Set the traveler object being sent on the pipeline.
     *
     * @param mixed $traveler
     *
     * @return $this
     */
    public function send($traveler)
    {

    }

    /**
     * Set the array of pipes.
     *
     * @param array|mixed $pipes
     *
     * @return $this
     */
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();

        return $this;
    }

    /**
     * Set the method to call on the pipes.
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

    }

    /**
     * Get the last stage executed in the pipeline
     *
     * @return Stage
     */
    public function getLastStage()
    {
        return $this->current;
    }
}
