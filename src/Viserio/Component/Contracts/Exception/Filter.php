<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Exception;

use Psr\Http\Message\RequestInterface;
use Throwable;

interface Filter
{
    /**
     * Filter and return the displayers.
     *
     * @param \Viserio\Component\Contracts\Exception\Displayer[] $displayers
     * @param \Psr\Http\Message\RequestInterface       $request
     * @param \Throwable                               $original
     * @param \Throwable                               $transformed
     * @param int                                      $code
     *
     * @return \Viserio\Component\Contracts\Exception\Displayer[]
     */
    public function filter(
        array $displayers,
        RequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array;
}
