<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver;

interface RequiresComponentConfig extends RequiresConfig
{
    /**
     * Returns the depth of the configuration array as a list. Can also be an empty array. For instance, the structure
     * of the getDimensions() method would be an array like.
     *
     * <code>
     *   return ['viserio', 'component', 'view'];
     * </code>
     *
     * @return iterable
     */
    public function getDimensions(): iterable;
}
