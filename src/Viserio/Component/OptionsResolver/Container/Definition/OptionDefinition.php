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

final class OptionDefinition extends AbstractOptionDefinition
{
    use DimensionsTrait;

    /**
     * The parameter key.
     *
     * @var string
     */
    private $key;

    /**
     * Create a new OptionDefinition instance.
     *
     * @param string      $key
     * @param string      $configClass
     * @param null|string $configId
     */
    public function __construct(string $key, string $configClass, string $configId = null)
    {
        parent::__construct($configClass, $configId);

        $this->key = $key;

        if ($this->reflection->implementsInterface(RequiresComponentConfigContract::class)) {
            $this->dimensions = $configClass::getDimensions();
        }
    }

    /**
     * Get option key.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->key;
    }
}
