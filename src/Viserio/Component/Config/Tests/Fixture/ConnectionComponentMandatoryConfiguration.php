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

namespace Viserio\Component\Config\Tests\Fixture;

use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;

class ConnectionComponentMandatoryConfiguration implements RequiresComponentConfigContract, RequiresMandatoryConfigContract
{
    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): iterable
    {
        return ['doctrine', 'connection'];
    }

    public static function getMandatoryConfig(): iterable
    {
        return ['orm_default'];
    }
}
