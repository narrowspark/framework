<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver;

/**
 * Code in this trait is taken from interop-config.
 *
 * @author Sandro Keil https://sandro-keil.de/blog/
 * @copyright Copyright (c) 2015-2017 Sandro Keil
 */
interface ProvidesDefaultOptions
{
    /**
     * Returns a list of default options, which are
     * merged in \Viserio\Component\OptionsResolver\Traits\AbstractOptionsResolverTrait::getResolvedConfig().
     *
     * @return iterable list with default options and values, can be nested
     */
    public static function getDefaultOptions(): iterable;
}
