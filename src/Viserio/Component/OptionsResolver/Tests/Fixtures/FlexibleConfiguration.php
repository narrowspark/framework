<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Fixtures;

use Interop\Config\ConfigurationTrait;
use Interop\Config\RequiresConfig;

class FlexibleConfiguration implements RequiresConfig
{
    use ConfigurationTrait;

    public function getDimensions(): iterable
    {
        return ['one', 'two', 'three', 'four'];
    }
}
