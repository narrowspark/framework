<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use ArrayIterator;
use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contract\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration implements RequiresComponentConfigIdContract, RequiresMandatoryOptionsContract
{
    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): iterable
    {
        return new ArrayIterator(['doctrine', 'connection']);
    }

    /**
     * {@inheritdoc}.
     */
    public static function getMandatoryOptions(): iterable
    {
        return new ArrayIterator(['params' => ['user', 'dbname'], 'driverClass']);
    }
}
