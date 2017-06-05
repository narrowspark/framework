<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectionMandatoryContainerIdConfiguration implements RequiresComponentConfigIdContract, RequiresMandatoryOptionsContract
{
    /**
     * {@inheritdoc}.
     */
    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }

    /**
     * {@inheritdoc}.
     */
    public function getMandatoryOptions(): iterable
    {
        return ['driverClass', 'params'];
    }
}
