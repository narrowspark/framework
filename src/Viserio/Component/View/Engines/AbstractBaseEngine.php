<?php
declare(strict_types=1);
namespace Viserio\Component\View\Engines;

use Viserio\Component\Contracts\OptionsResolver\RequiresConfig;
use Viserio\Component\Contracts\OptionsResolver\RequiresMandatoryOptions;
use Viserio\Component\Contracts\View\Engine as EngineContract;

abstract class AbstractBaseEngine implements EngineContract, RequiresConfig, RequiresMandatoryOptions
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
