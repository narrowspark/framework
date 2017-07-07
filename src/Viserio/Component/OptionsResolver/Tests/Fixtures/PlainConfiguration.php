<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Fixtures;

use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

class PlainConfiguration implements RequiresComponentConfigContract
{
    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): iterable
    {
        return [];
    }
}
