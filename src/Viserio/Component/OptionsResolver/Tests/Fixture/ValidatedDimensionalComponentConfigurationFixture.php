<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Exception;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresValidatedConfig as RequiresValidatedConfigContract;

class ValidatedDimensionalComponentConfigurationFixture implements RequiresComponentConfigContract, ProvidesDefaultOptionsContract, RequiresValidatedConfigContract, RequiresMandatoryOptionsContract
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
        return [
            'foo' => [
                'maxLength',
            ],
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getOptionValidators(): array
    {
        return [
            'minLength' => static function ($value): void {
                if (! \is_int($value)) {
                    throw new Exception('Value is not a int.');
                }
            },
            'foo' => [
                'maxLength' => static function ($value): void {
                    if (! \is_int($value)) {
                        throw new Exception('Value is not a int.');
                    }
                },
            ],
        ];
    }
}
