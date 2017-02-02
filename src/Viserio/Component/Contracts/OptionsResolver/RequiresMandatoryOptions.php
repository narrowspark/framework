<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver;

interface RequiresMandatoryOptions
{
    /**
     * Returns a list of mandatory options which must be available.
     *
     * @return iterable List with mandatory options, can be nested
     */
    public function getMandatoryOptions(): iterable;
}
