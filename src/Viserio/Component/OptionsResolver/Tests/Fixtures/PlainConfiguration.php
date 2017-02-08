<?php
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;

class PlainConfiguration implements RequiresConfig
{
    use ConfigurationTrait;

    /**
     * @interitdoc
     */
    public function getDimensions(): iterable
    {
        return [];
    }
}
<?php
namespace InteropTest\Config\TestAsset;
