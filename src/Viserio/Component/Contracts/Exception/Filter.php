<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\Exception;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

interface Filter
{
    /**
     * Filter and return the displayers.
     *
     * @param \Viserio\Component\Contracts\Exception\Displayer[] $displayers
     * @param \Psr\Http\Message\ServerRequestInterface           $request
     * @param \Throwable                                         $original
     * @param \Throwable                                         $transformed
     * @param int                                                $code
     *
     * @return \Viserio\Component\Contracts\Exception\Displayer[]
     */
    public function filter(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array;
}
