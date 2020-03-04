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
use Viserio\Component\Config\Tests\Fixture\PackageDefaultAndMandatoryConfigComponentConfiguration;
use Viserio\Component\Config\Tests\Unit\Traits\ConfigurationDimensionsIteratorTestTrait;
use Viserio\Contract\Config\Exception\DimensionNotFoundException;

/**
 * @internal
 *
 * @small
 *
 * @covers \Viserio\Component\Config\ConfigurationDimensionsIterator
 */
final class ConfigurationDimensionsIteratorTest extends TestCase
{
    use ConfigurationDimensionsIteratorTestTrait;

    public function testDimensionsResolvingShouldThrowAExceptionIfMandatoryConfigWithDefaultConfigAndNoConfigurationIsSet(): void
    {
        $this->expectException(DimensionNotFoundException::class);
        $this->expectExceptionMessage('No dimension configuration was set or found for [["vendor"]] in class [Viserio\Component\Config\Tests\Fixture\PackageDefaultAndMandatoryConfigComponentConfiguration].');

        new ConfigurationDefaultIterator(
            PackageDefaultAndMandatoryConfigComponentConfiguration::class,
            $this->getConfigurationIterator(
                PackageDefaultAndMandatoryConfigComponentConfiguration::class,
                new ArrayIterator([])
            )
        );
    }

    /**
     * @param ArrayIterator $iterator
     */
    protected function getConfigurationIterator(string $class, $iterator, ?string $id = null): IteratorIterator
    {
        return new ConfigurationDimensionsIterator($class, $iterator, $id);
    }
}
