<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Contract\Pipeline;

use Closure;

interface Pipeline
{
    /**
     * Set the traveler object being sent on the pipeline.
     */
    public function send($traveler): self;

    /**
     * Set the array of stages.
     *
     * @param array|mixed $stages
     */
    public function through($stages): self;

    /**
     * Run the pipeline with a final destination callback.
     */
    public function then(Closure $destination);

    /**
     * Set the method to call on the stages.
     */
    public function via(string $method): self;
}
