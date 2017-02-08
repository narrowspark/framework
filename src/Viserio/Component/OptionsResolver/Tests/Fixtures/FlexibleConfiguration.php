<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig as RequiresConfigContract;

class FlexibleConfiguration implements RequiresConfigContract
{
    public function getDimensions(): iterable
    {
        return ['one', 'two', 'three', 'four'];
    }
}
