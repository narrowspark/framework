<?php

declare(strict_types=1);

/**
 * This file is part of Narrowspark Framework.
 *
 * (c) Daniel Bannert <d.bannert@anolilab.de>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Viserio\Component\View\Engine;

use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;
use Viserio\Contract\View\Engine as EngineContract;

abstract class AbstractBaseEngine implements EngineContract,
    RequiresComponentConfigContract,
    RequiresMandatoryConfigContract
{
    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): iterable
    {
        return ['viserio', 'view'];
    }

    /**
     * {@inheritdoc}
     */
    public static function getMandatoryConfig(): iterable
    {
        return [
            'paths',
            'engines',
        ];
    }
}
