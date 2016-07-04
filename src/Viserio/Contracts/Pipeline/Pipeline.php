<?php
namespace Viserio\Contracts\Pipeline;

use Closure;

interface Pipeline
{
    /**
     * Set the traveler object being sent on the pipeline.
     *
     * @param mixed $traveler
     *
     * @return $this
     */
    public function send($traveler): Pipeline;

    /**
     * Set the array of stages.
     *
     * @param array|mixed $stages
     *
     * @return $this
     */
    public function through($stages): Pipeline;

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param Closure $destination
     *
     * @return mixed
     */
    public function then(Closure $destination);

    /**
     * Set the method to call on the stages.
     *
     * @param string $method
     *
     * @return $this
     */
    public function via(string $method): Pipeline;
}
