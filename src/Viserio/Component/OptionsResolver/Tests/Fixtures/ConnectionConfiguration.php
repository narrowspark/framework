<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;

class ConnectionConfiguration implements RequiresConfig
{
    use ConfigurationTrait;

    /**
     * @interitdoc
     */
    public function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }
}
