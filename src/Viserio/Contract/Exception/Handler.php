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

namespace Viserio\Contract\Exception;

use Throwable;
use Viserio\Contract\Exception\Transformer as TransformerContract;

interface Handler
{
    /**
     * Determine if the exception shouldn't be reported.
     */
    public function addShouldntReport(Throwable $exception): self;

    /**
     * Report or log an exception.
     */
    public function report(Throwable $exception): void;

    /**
     * Add the transformed instance.
     */
    public function addTransformer(TransformerContract $transformer): self;

    /**
     * Get the transformer exceptions.
     */
    public function getTransformers(): array;
}
