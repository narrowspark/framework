<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver;

/**
 * Code in this trait is taken from interop-config.
 *
 * @author Sandro Keil https://sandro-keil.de/blog/
 * @copyright Copyright (c) 2015-2017 Sandro Keil
 */
interface RequiresComponentConfig extends RequiresConfig
{
    /**
     * Returns the depth of the configuration array as a list. Can also be an empty array. For instance, the structure
     * of the getDimensions() method would be an array like.
     *
     * <code>
     *     return ['viserio', 'component', 'view'];
     * </code>
     *
     * @return iterable
     */
    public function getDimensions(): iterable;
}
