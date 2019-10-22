<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Contract\Exception;

use Throwable;
use Viserio\Contract\Exception\Transformer as TransformerContract;

interface Handler
{
    /**
     * Determine if the exception shouldn't be reported.
     *
     * @param \Throwable $exception
     *
     * @return self
     */
    public function addShouldntReport(Throwable $exception): self;

    /**
     * Report or log an exception.
     *
     * @param \Throwable $exception
     *
     * @return void
     */
    public function report(Throwable $exception): void;

    /**
     * Add the transformed instance.
     *
     * @param \Viserio\Contract\Exception\Transformer $transformer
     *
     * @return self
     */
    public function addTransformer(TransformerContract $transformer): self;

    /**
     * Get the transformer exceptions.
     *
     * @return array
     */
    public function getTransformers(): array;
}
