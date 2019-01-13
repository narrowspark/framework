<?php
declare(strict_types=1);
namespace Viserio\Component\Http\Response\Traits;

trait InjectContentTypeTrait
{
    /**
     * Inject the provided Content-Type, if none is already present.
     *
     * @param string $contentType
     * @param array  $headers
     *
     * @return array Headers with injected Content-Type
     */
    private function injectContentType(string $contentType, array $headers): array
    {
        $hasContentType = \array_reduce(\array_keys($headers), static function ($carry, $item) {
            return $carry ?: (\strtolower($item) === 'content-type');
        }, false);

        if (! $hasContentType) {
            $headers['Content-Type'] = [$contentType];
        }

        return $headers;
    }
}
