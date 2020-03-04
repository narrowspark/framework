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

namespace Viserio\Component\Config\Tests\Unit;

use ArrayIterator;
use IteratorIterator;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ConfigurationDefaultIterator;
use Viserio\Component\Config\ConfigurationDimensionsIterator;
use Viserio\Component\Config\ConfigurationValidatorIterator;
use Viserio\Component\Config\Tests\Unit\Traits\ConfigurationValidatorIteratorTestTrait;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\ConfigurationDefaultIterator
 * @covers \Viserio\Component\Config\ConfigurationDimensionsIterator
 * @covers \Viserio\Component\Config\ConfigurationValidatorIterator
 */
final class ConfigurationValidatorIteratorTest extends TestCase
{
    use ConfigurationValidatorIteratorTestTrait;

    protected function getConfigurationIterator(
        string $class,
        ArrayIterator $iterator,
        ?string $id = null
    ): IteratorIterator {
        $interfaces = \class_implements($class);

        if (\array_key_exists(RequiresComponentConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationDimensionsIterator($class, $iterator, $id);
        }

        $iterator = new ConfigurationValidatorIterator(
            $class,
            $iterator
        );

        if (\array_key_exists(ProvidesDefaultConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationDefaultIterator($class, $iterator);
        }

        return $iterator;
    }
}
