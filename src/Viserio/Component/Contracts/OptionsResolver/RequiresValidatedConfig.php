<?php
declare(strict_types=1);
namespace Viserio\Component\Contracts\OptionsResolver;

interface RequiresValidatedConfig
{
    /**
     * Returns a list of option validator callables which must be available in getMandatoryOptions.
     *
     * @return array
     */
    public function getOptionValidators(): array;
}
