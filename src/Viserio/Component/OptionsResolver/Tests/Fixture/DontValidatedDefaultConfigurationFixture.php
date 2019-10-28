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

use Exception;
use Viserio\Contract\OptionsResolver\ProvidesDefaultOption as ProvidesDefaultOptionContract;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\OptionsResolver\RequiresMandatoryOption as RequiresMandatoryOptionContract;
use Viserio\Contract\OptionsResolver\RequiresValidatedOption as RequiresValidatedOptionContract;

class DontValidatedDefaultConfigurationFixture implements ProvidesDefaultOptionContract,
    RequiresComponentConfigContract,
    RequiresMandatoryOptionContract,
    RequiresValidatedOptionContract
{
    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): array
    {
        return ['vendor', 'package'];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getDefaultOptions(): array
    {
        return [
            'minLength' => 2,
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getMandatoryOptions(): array
    {
        return ['maxLength'];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getOptionValidators(): array
    {
        return [
            'minLength' => static function ($value): void {
                throw new Exception('Dont throw exception on default values.');
            },
            'maxLength' => static function ($value): void {
                if (! \is_int($value)) {
                    throw new Exception('Value is not a int.');
                }
            },
        ];
    }
}
