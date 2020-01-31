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

namespace Viserio\Component\Config;

use ArrayAccess;
use ArrayIterator;
use Iterator;
use IteratorIterator;
use Traversable;
use Viserio\Contract\Config\Exception\DimensionNotFoundException;
use Viserio\Contract\Config\Exception\InvalidArgumentException;
use Viserio\Contract\Config\Exception\UnexpectedValueException;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfigId as RequiresComponentConfigIdContract;
use Viserio\Contract\Config\RequiresConfigId as RequiresConfigIdContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;

final class ConfigurationDimensionsIterator extends IteratorIterator
{
    /**
     * List of dimensions.
     *
     * @var array<int, string>
     */
    private array $dimensions;

    /**
     * Create a new ConfigurationDimensionsIterator instance.
     *
     * @param string      $class
     * @param Traversable $iterator
     * @param null|string $id
     */
    public function __construct(string $class, Traversable $iterator, ?string $id = null)
    {
        $dimensions = $class::getDimensions();
        $this->dimensions = $dimensions instanceof Iterator ? \iterator_to_array($dimensions) : (array) $dimensions;
        $interfaces = \class_implements($class);

        if (\array_key_exists(RequiresConfigIdContract::class, $interfaces) || \array_key_exists(RequiresComponentConfigIdContract::class, $interfaces)) {
            $this->dimensions[] = $id;
        } elseif ($id !== null) {
            throw new InvalidArgumentException(\sprintf('The class [%s] does not support multiple instances.', $class));
        }

        $array = \iterator_to_array($iterator);

        foreach ($this->dimensions as $dimension) {
            if ((array) $array !== $array && ! $array instanceof ArrayAccess) {
                throw new UnexpectedValueException($this->dimensions, $dimension);
            }

            if (! isset($array[$dimension])) {
                if (! \array_key_exists(RequiresMandatoryConfigContract::class, $interfaces)
                    && \array_key_exists(ProvidesDefaultConfigContract::class, $interfaces)
                ) {
                    break;
                }

                throw new DimensionNotFoundException($class, $dimension, $id);
            }

            $array = $array[$dimension];
        }

        if ((array) $array !== $array
            && ! $array instanceof ArrayAccess
            && \array_key_exists(RequiresComponentConfigIdContract::class, $interfaces)
        ) {
            throw new UnexpectedValueException($class::getDimensions());
        }

        parent::__construct(new ArrayIterator($array));
    }

    /**
     * @return array
     */
    public function getDimensions(): array
    {
        return $this->dimensions;
    }
}
