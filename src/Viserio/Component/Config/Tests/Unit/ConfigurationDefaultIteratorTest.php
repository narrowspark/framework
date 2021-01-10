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

use IteratorIterator;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ConfigurationDefaultIterator;
use Viserio\Component\Config\ConfigurationDimensionsIterator;
use Viserio\Component\Config\Tests\Unit\Traits\ConfigurationDefaultIteratorTestTrait;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\ConfigurationDefaultIterator
 * @covers \Viserio\Component\Config\ConfigurationDimensionsIterator
 */
final class ConfigurationDefaultIteratorTest extends TestCase
{
    use ConfigurationDefaultIteratorTestTrait;

    protected function getConfigurationIterator(string $class, $iterator, ?string $id = null): IteratorIterator
    {
        $interfaces = \class_implements($class);

        if (\array_key_exists(RequiresComponentConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationDimensionsIterator($class, $iterator, $id);
        }

        return new ConfigurationDefaultIterator($class, $iterator);
    }
}
