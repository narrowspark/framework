<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Exception;
use Viserio\Component\Contracts\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

class ValidateDefaultValueOnOverwriteFixture implements RequiresComponentConfigContract, ProvidesDefaultOptionsContract, RequiresValidatedConfigContract, RequiresMandatoryOptionsContract
{
    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): iterable
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
    public static function getMandatoryOptions(): iterable
    {
        return ['maxLength'];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getOptionValidators(): array
    {
        return [
            'minLength' => function ($value) {
                if (! is_int($value)) {
                    throw new Exception('Value is not a int.');
                }
            },
            'maxLength' => function ($value) {
                if (! is_int($value)) {
                    throw new Exception('Value is not a int.');
                }
            },
        ];
    }
}
