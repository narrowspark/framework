<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\Contract\OptionsResolver\DeprecatedOptions as DeprecatedOptionsContract;
use Viserio\Component\Contract\OptionsResolver\ProvidesDefaultOptions as ProvidesDefaultOptionsContract;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectionComponentDefaultOptionsMandatoryContainedIdWithDeprecationKeyConfiguration implements RequiresComponentConfigIdContract, DeprecatedOptionsContract, RequiresMandatoryOptionsContract, ProvidesDefaultOptionsContract
{
    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): array
    {
        return ['doctrine', 'connection'];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getMandatoryOptions(): array
    {
        return ['driverClass'];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getDefaultOptions(): array
    {
        return [
            'params' => [
                'host' => 'awesomehost',
                'port' => '4444',
            ],
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getDeprecatedOptions(): array
    {
        return [
            'driverClass',
        ];
    }
}
