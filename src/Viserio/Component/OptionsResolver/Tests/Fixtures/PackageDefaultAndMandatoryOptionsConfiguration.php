<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class PackageDefaultAndMandatoryOptionsConfiguration implements RequiresComponentConfigContract, ProvidesDefaultOptionsContract, RequiresMandatoryOptionsContract
{
    /**
     * @interitdoc
     */
    public function getDimensions(): iterable
    {
        return ['vendor', 'package'];
    }

    /**
     * @interitdoc
     */
    public function getDefaultOptions(): array
    {
        return [
            'minLength' => 2,
            'maxLength' => 10,
        ];
    }

    /**
     * @interitdoc
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'callback',
        ];
    }
}
