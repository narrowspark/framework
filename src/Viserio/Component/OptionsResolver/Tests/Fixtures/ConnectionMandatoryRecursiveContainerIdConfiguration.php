<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use ArrayIterator;
use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectionMandatoryRecursiveContainerIdConfiguration implements RequiresComponentConfigIdContract, RequiresMandatoryOptionsContract
{
    /**
     * {@inheritdoc}.
     */
    public function getDimensions(): iterable
    {
        return new ArrayIterator(['doctrine', 'connection']);
    }

    /**
     * {@inheritdoc}.
     */
    public function getMandatoryOptions(): iterable
    {
        return new ArrayIterator(['params' => ['user', 'dbname'], 'driverClass']);
    }
}
