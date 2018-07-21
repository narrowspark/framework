<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectionComponentMandatoryConfiguration implements RequiresComponentConfigContract, RequiresMandatoryOptionsContract
{
    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): array
    {
        return ['doctrine', 'connection'];
    }

    public static function getMandatoryOptions(): array
    {
        return ['orm_default'];
    }
}
