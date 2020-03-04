<?php

declare(strict_types=1);

/**
 * Copyright (c) 2018-2020 Daniel Bannert
 *
 * For the full copyright and license information, please view
 * the LICENSE.md file that was distributed with this source code.
 *
 * @see https://github.com/narrowspark/automatic
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
