<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Filter;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Viserio\Component\Contract\Exception\Filter as FilterContract;

class ContentTypeFilter implements FilterContract
{
    /**
     * {@inheritdoc}
     */
    public function filter(
        array $displayers,
        ServerRequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        foreach ($displayers as $index => $displayer) {
            if (! $request->getHeaderLine('Content-Type') !== $displayer->getContentType()) {
                unset($displayers[$index]);
            }
        }

        return \array_values($displayers);
    }
}
