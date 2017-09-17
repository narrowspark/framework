<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\Exception;

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
