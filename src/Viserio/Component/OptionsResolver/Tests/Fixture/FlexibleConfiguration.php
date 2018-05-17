<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixture;

use Viserio\Component\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

class FlexibleConfiguration implements RequiresComponentConfigContract
{
    public static function getDimensions(): iterable
    {
        return ['one', 'two', 'three', 'four'];
    }
}
