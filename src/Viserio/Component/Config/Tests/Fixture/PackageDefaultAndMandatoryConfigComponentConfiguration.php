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

use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;

class PackageDefaultAndMandatoryConfigComponentConfiguration implements ProvidesDefaultConfigContract, RequiresComponentConfigContract, RequiresMandatoryConfigContract
{
    /**
     * {@inheritdoc}.
     */
    public static function getDimensions(): iterable
    {
        return ['vendor', 'package'];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getDefaultConfig(): iterable
    {
        return [
            'minLength' => 2,
            'maxLength' => 10,
        ];
    }

    /**
     * {@inheritdoc}.
     */
    public static function getMandatoryConfig(): iterable
    {
        return [
            'callback',
        ];
    }
}
