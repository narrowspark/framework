<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

class ConnectionDefaultOptionsWithMandatoryNullValueConfigurationAndStringValidator implements
    RequiresConfigContract,
    RequiresMandatoryOptionsContract,
    RequiresValidatedConfigContract
{
    public static function getMandatoryOptions(): array
    {
        return ['driverClass'];
    }

    public static function getOptionValidators(): array
    {
        return [
            'driverClass' => ['string'],
        ];
    }
}
