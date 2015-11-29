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
    public function send($traveler);

    /**
     * Set the array of stages.
     *
     * @param array|mixed $stages
     *
     * @return self
     */
    public function through($stages);

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param callable $destination
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
    public function via($method);
}
