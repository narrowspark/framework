<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Traits;

use InvalidArgumentException;

trait ValidateLocaleTrait
{
    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    protected function assertValidLocale(string $locale): void
    {
        if (\preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale) !== 1) {
            throw new InvalidArgumentException(\sprintf('Invalid [%s] locale.', $locale));
        }
    }
}
