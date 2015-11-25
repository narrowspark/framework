<?php
namespace Viserio\Contracts\Pipeline;

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

use \Closure;

/**
 * Pipeline.
 *
 * @author  Daniel Bannert
 *
 * @since   0.10.0
 */
interface Pipeline
{
    /**
     * Set the traveler object being sent on the pipeline.
     *
     * @param mixed $traveler
     *
     * @return $this
     */
    public function send($traveler);

    /**
     * Set the stops of the pipeline.
     *
     * @param dynamic|array $stops
     *
     * @return $this
     */
    public function through($stops);

    /**
     * Set the method to call on the stops.
     *
     * @param string $method
     *
     * @return $this
     */
    public function via($method);

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param \Closure $destination
     *
     * @return mixed
     */
    public function then(Closure $destination);

    /**
     * Indicates that all stages executed in the pipeline.
     *
     * @return bool
     */
    public function ended();

    /**
     * Get the last stage executed in the pipeline
     *
     * @return Stage
     */
    public function getLastStage();
}
