<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

class FlexibleComponentConfiguration implements RequiresComponentConfigContract
{
    public static function getDimensions(): array
    {
        return ['one', 'two', 'three', 'four'];
    }
}
