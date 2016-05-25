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
     * @return self
     */
    public function send(string $traveler): Pipeline;

    /**
     * Set the array of stages.
     *
     * @param array|mixed $stages
     *
     * @return self
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
     * @return self
     */
    public function via(string $method): Pipeline;
}
