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

use Viserio\Contract\OptionsResolver\Exception\InvalidArgumentException;
use Viserio\Contract\OptionsResolver\RequiresComponentConfig as RequiresComponentConfigContract;

final class DimensionsOptionDefinition extends AbstractOptionDefinition implements RequiresComponentConfigContract
{
    /**
     * Array of dimension names.
     *
     * @internal
     *
     * @var array
     */
    public static $classDimensions = [];

    /**
     * Array of dimension names.
     *
     * @var array
     */
    private $dimensions;

    /**
     * Create a new DimensionsOptionDefinition instance.
     *
     * @param string      $configClass
     * @param null|string $configId
     */
    public function __construct(string $configClass, string $configId = null)
    {
        $interfaces = \class_implements($configClass);

        if (! isset($interfaces[RequiresComponentConfigContract::class])) {
            throw new InvalidArgumentException(\sprintf('Config class [%s] didn\'t implement the [%s] interface.', $configClass, RequiresComponentConfigContract::class));
        }

        parent::__construct($configClass, $configId);

        $this->dimensions = $configClass::getDimensions();
    }

    /**
     * Return the options aware class.
     *
     * @return array
     */
    public function getClassDimensions(): array
    {
        return $this->dimensions;
    }

    /**
     * {@inheritdoc}
     */
    public static function getDimensions(): array
    {
        return self::$classDimensions;
    }
}
