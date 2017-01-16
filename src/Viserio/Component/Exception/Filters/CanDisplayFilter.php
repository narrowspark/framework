<?php
declare(strict_types=1);
namespace Viserio\Component\Exception\Filters;

use Psr\Http\Message\RequestInterface;
use Throwable;
use Viserio\Component\Contracts\Exception\Filter as FilterContract;

class CanDisplayFilter implements FilterContract
{
    /**
     * {@inheritdoc}
     */
    public function filter(
        array $displayers,
        RequestInterface $request,
        Throwable $original,
        Throwable $transformed,
        int $code
    ): array {
        foreach ($displayers as $index => $displayer) {
            if (! $displayer->canDisplay($original, $transformed, $code)) {
                unset($displayers[$index]);
            }
        }

        return array_values($displayers);
    }
}
