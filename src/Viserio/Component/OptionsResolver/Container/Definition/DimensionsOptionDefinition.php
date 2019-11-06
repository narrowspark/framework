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

namespace Viserio\Component\OptionsResolver\Container\Definition;

use Viserio\Component\OptionsResolver\Container\Definition\Traits\DimensionsTrait;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

final class DimensionsOptionDefinition extends AbstractOptionDefinition implements RequiresComponentConfigContract
{
    use DimensionsTrait;

    /** @var string */
    protected static $interfaceCheckName = RequiresComponentConfigContract::class;

    /**
     * Create a new DimensionsOptionDefinition instance.
     *
     * @param string      $configClass
     * @param null|string $configId
     */
    public function __construct(string $configClass, ?string $configId = null)
    {
        parent::__construct($configClass, $configId);

        $this->dimensions = $configClass::getDimensions();
    }
}
