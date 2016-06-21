<?php
namespace Viserio\Contracts\Exception;

interface Transformer
{
    /**
     * Transform the provided exception.
     *
     * @param \Exception|\Throwable $exception
     *
     * @return \Exception|\Throwable
     */
    public function transform($exception);
}
