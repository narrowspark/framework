<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
 */

namespace Viserio\Component\Http\Response\Traits;

trait InjectContentTypeTrait
{
    /**
     * Inject the provided Content-Type, if none is already present.
     *
     * @param array<int|string, mixed> $headers
     *
     * @return array<int|string, mixed> Headers with injected Content-Type
     */
    private function injectContentType(string $contentType, array $headers): array
    {
        $hasContentType = \array_reduce(\array_keys($headers), static function (bool $carry, string $item): bool {
            if ($carry) {
                return true;
            }

            return \strtolower($item) === 'content-type';
        }, false);

        if (! $hasContentType) {
            $headers['Content-Type'] = [$contentType];
        }

        return $headers;
    }
}
