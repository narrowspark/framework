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
     * @param array|Traversable $iterator
     *
     * @throws \Viserio\Contract\Config\Exception\MandatoryConfigNotFoundException
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
