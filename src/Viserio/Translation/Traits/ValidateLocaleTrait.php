<?php
namespace Viserio\Translation\Traits;

use InvalidArgumentException;

trait ValidateLocaleTrait
{
    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @return void
     *
     * @throws \InvalidArgumentException If the locale contains invalid characters
     */
    protected function assertValidLocale(string $locale)
    {
        if (preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid "%s" locale.', $locale));
        }
    }
}
