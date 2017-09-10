<?php
declare(strict_types=1);
namespace Viserio\Component\Translation\Traits;

use Viserio\Component\Contract\Translation\Exception\InvalidArgumentException;

trait ValidateLocaleTrait
{
    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @throws \Viserio\Component\Contract\Translation\Exception\InvalidArgumentException If the locale contains invalid characters
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
