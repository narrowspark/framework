<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engines;

use Viserio\Component\Contracts\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions as RequiresMandatoryOptionsContract;
use Viserio\Component\Contracts\View\Engine as EngineContract;

abstract class AbstractBaseEngine implements EngineContract, RequiresComponentConfigContract, RequiresMandatoryOptionsContract
{
    /**
     * {@inheritdoc}
     */
    public function getDimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMandatoryOptions(): iterable
    {
        return [
            'paths',
            'engines',
        ];
    }
}
