<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Exception;

use Throwable;
use Viserio\Component\Contract\Exception\Transformer as TransformerContract;

interface Handler
{
    /**
     * Determine if the exception shouldn't be reported.
     *
     * @param \Throwable $exception
     *
     * @return \Viserio\Component\Contract\Exception\Handler
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
     * @param \Viserio\Component\Contract\Exception\Transformer $transformer
     *
     * @return \Viserio\Component\Contract\Exception\Handler
     */
    public function addTransformer(TransformerContract $transformer): self;

    /**
     * Get the transformer exceptions.
     *
     * @return array
     */
    public function getTransformers(): array;
}
