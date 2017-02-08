<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Fixtures;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfigId;
use Interop\Config\RequiresMandatoryOptions;

class ConnectionMandatoryRecursiveContainerIdConfiguration implements RequiresConfigId, RequiresMandatoryOptions
{
    use ConfigurationTrait;

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
