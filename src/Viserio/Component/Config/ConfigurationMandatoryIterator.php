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

use Iterator;
use IteratorIterator;
use Traversable;
use Viserio\Contract\Config\Exception\MandatoryConfigNotFoundException;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;

class ConfigurationMandatoryIterator extends IteratorIterator
{
    /**
     * Create a new ConfigurationMandatoryIterator instance.
     *
     * @param string      $class
     * @param Traversable $iterator
     */
    public function __construct(string $class, Traversable $iterator)
    {
        $interfaces = \class_implements($class);

        $mandatory = $class::getMandatoryConfig();
        $mandatory = $mandatory instanceof Iterator ? \iterator_to_array($mandatory) : (array) $mandatory;

        $dimensions = [];

        if (\array_key_exists(RequiresComponentConfigContract::class, $interfaces)) {
            $dimensions = $class::getDimensions();
            $dimensions = $dimensions instanceof Iterator ? \iterator_to_array($dimensions) : (array) $dimensions;
        }

        $this->validateMandatoryConfig(\iterator_to_array($iterator), $dimensions, $mandatory);

        parent::__construct($iterator);
    }

    /**
     * Validate if config needs mandatory config.
     *
     * @param array|\Traversable $iterator
     * @param iterable           $dimensions
     * @param iterable           $mandatory
     *
     * @throws \Viserio\Contract\Config\Exception\MandatoryConfigNotFoundException
     *
     * @return void
     */
    private function validateMandatoryConfig($iterator, iterable $dimensions, iterable $mandatory): void
    {
        foreach ($mandatory as $key => $value) {
            $useRecursion = ! \is_scalar($value);

            if (! $useRecursion && \is_array($iterator) && \array_key_exists($value, $iterator)) {
                continue;
            }

            if ($useRecursion && \array_key_exists($key, $iterator)) {
                $this->validateMandatoryConfig($iterator[$key], $dimensions, $value);

                return;
            }

            throw new MandatoryConfigNotFoundException($dimensions, $useRecursion ? $key : $value);
        }
    }
}
