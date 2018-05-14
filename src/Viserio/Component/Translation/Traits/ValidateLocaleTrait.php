<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
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
     *
     * @return void
     */
    protected static function assertValidLocale(string $locale): void
    {
        if (\preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale) !== 1) {
            throw new InvalidArgumentException(\sprintf('Invalid [%s] locale.', $locale));
        }
    }
}
