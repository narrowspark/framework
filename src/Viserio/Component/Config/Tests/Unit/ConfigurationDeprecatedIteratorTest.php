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

namespace Viserio\Component\Config\Tests\Unit;

use IteratorIterator;
use PHPUnit\Framework\TestCase;
use Viserio\Component\Config\ConfigurationDefaultIterator;
use Viserio\Component\Config\ConfigurationDeprecatedIterator;
use Viserio\Component\Config\ConfigurationDimensionsIterator;
use Viserio\Component\Config\ConfigurationMandatoryIterator;
use Viserio\Component\Config\Tests\Unit\Traits\ConfigurationDeprecatedIteratorTestTrait;
use Viserio\Contract\Config\ProvidesDefaultConfig as ProvidesDefaultConfigContract;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;
use Viserio\Contract\Config\RequiresMandatoryConfig as RequiresMandatoryConfigContract;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\ConfigurationDefaultIterator
 * @covers \Viserio\Component\Config\ConfigurationDeprecatedIterator
 */
final class ConfigurationDeprecatedIteratorTest extends TestCase
{
    use ConfigurationDeprecatedIteratorTestTrait;

    protected function getConfigurationIterator(string $class, $iterator, ?string $id = null): IteratorIterator
    {
        $interfaces = \class_implements($class);

        if (\array_key_exists(RequiresComponentConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationDimensionsIterator($class, $iterator, $id);
        }

        if (\array_key_exists(RequiresMandatoryConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationMandatoryIterator($class, $iterator);
        }

        if (\array_key_exists(ProvidesDefaultConfigContract::class, $interfaces)) {
            $iterator = new ConfigurationDefaultIterator($class, $iterator);
        }

        return new ConfigurationDeprecatedIterator($class, $iterator);
    }
}
