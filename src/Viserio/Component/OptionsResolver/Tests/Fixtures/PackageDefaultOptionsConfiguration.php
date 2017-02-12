<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;

class PackageDefaultOptionsConfiguration implements RequiresConfigContract, ProvidesDefaultOptionsContract
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
}
