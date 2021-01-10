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

namespace Viserio\Component\Translation\Traits;

use Viserio\Contract\Translation\Exception\InvalidArgumentException;

trait ValidateLocaleTrait
{
    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @throws \Viserio\Contract\Translation\Exception\InvalidArgumentException If the locale contains invalid characters
     */
    protected static function assertValidLocale(string $locale): void
    {
        if (\preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale) !== 1) {
            throw new InvalidArgumentException(\sprintf('Invalid [%s] locale.', $locale));
        }
    }
}
