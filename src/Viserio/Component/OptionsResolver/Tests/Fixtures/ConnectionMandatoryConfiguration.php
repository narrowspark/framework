<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectionMandatoryConfiguration implements RequiresConfigContract, RequiresMandatoryOptions
{
    /**
     * @interitdoc
     */
    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }

    public function getMandatoryOptions(): iterable
    {
        return ['orm_default'];
    }
}
