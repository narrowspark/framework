<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\OptionsResolver;

interface RequiresValidatedConfig extends RequiresConfig
{
    /**
     * Returns a list of callable validators
     * which key should be available in getMandatoryOptions or getDefaultOptions.
     *
     * @return array
     */
    public static function getOptionValidators(): array;
}
