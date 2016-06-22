<?php
namespace Viserio\Contracts\Exception;

use Throwable;

interface Transformer
{
    /**
     * Transform the provided exception.
     *
     * @param \Throwable $exception
     *
     * @return \Throwable
     */
    public function transform(Throwable $exception): Throwable;
}
