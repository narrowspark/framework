<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Pipeline;

use Closure;

interface Pipeline
{
    /**
     * Set the traveler object being sent on the pipeline.
     *
     * @param mixed $traveler
     *
     * @return \Viserio\Component\Contract\Pipeline\Pipeline
     */
    public function send($traveler): self;

    /**
     * Set the array of stages.
     *
     * @param array|mixed $stages
     *
     * @return \Viserio\Component\Contract\Pipeline\Pipeline
     */
    public function through($stages): self;

    /**
     * Run the pipeline with a final destination callback.
     *
     * @param \Closure $destination
     *
     * @return mixed
     */
    public function then(Closure $destination);

    /**
     * Set the method to call on the stages.
     *
     * @param string $method
     *
     * @return \Viserio\Component\Contract\Pipeline\Pipeline
     */
    public function via(string $method): self;
}
