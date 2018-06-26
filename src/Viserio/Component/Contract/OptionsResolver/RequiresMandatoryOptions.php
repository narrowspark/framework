<?php
declare(strict_types=1);
namespace Viserio\Component\Contract\OptionsResolver;

/**
 * Code in this trait is taken from interop-config.
 *
 * @author Sandro Keil https://sandro-keil.de/blog/
 * @copyright Copyright (c) 2015-2017 Sandro Keil
 */
interface RequiresMandatoryOptions
{
    /**
     * Returns a list of mandatory options which must be available.
     *
     * @return array List with mandatory options, can be nested
     */
    public static function getMandatoryOptions(): array;
}
