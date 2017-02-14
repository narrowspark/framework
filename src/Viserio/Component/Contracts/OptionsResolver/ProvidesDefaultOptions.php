<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver;

interface ProvidesDefaultOptions
{
    /**
     * Returns a list of default options, which are
     * merged in \Viserio\Component\Contracts\OptionsResolver\Resolver::resolve().
     *
     * @return iterable list with default options and values, can be nested.
     */
    public function getDefaultOptions(): iterable;
}
