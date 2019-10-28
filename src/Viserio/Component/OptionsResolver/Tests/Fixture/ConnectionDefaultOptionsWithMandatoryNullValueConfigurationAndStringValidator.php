<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Contract\OptionsResolver\RequiresConfig as RequiresConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

class ConnectionDefaultOptionsWithMandatoryNullValueConfigurationAndStringValidator implements RequiresConfigContract,
    RequiresMandatoryOptionContract,
    RequiresValidatedOptionContract
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
