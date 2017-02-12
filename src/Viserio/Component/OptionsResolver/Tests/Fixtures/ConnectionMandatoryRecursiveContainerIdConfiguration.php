<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;

class ConnectionMandatoryRecursiveContainerIdConfiguration implements RequiresConfigIdContract, RequiresMandatoryOptionsContract
{
    /**
     * @interitdoc
     */
    public function getDimensions(): iterable
    {
        return new \ArrayIterator(['doctrine', 'connection']);
    }

    /**
     * @interitdoc
     */
    public function getMandatoryOptions(): iterable
    {
        return new \ArrayIterator(['params' => ['user', 'dbname'], 'driverClass']);
    }
}
