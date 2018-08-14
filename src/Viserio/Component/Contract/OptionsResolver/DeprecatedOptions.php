<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\OptionsResolver;

interface DeprecatedOptions
{
    /**
     * Deprecate options that will be no more used in the next version.
     * Key should be available in getMandatoryOptions or getDefaultOptions.
     *
     * The deprecation message supports a sprintf replacer for the key.
     *
     * @return array
     */
    public static function getDeprecatedOptions(): array;
}
