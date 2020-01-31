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
     * @param string        $class
     * @param ArrayIterator $iterator
     * @param null|string   $id
     *
     * @return IteratorIterator
     */
    protected function getConfigurationIterator(string $class, $iterator, ?string $id = null): IteratorIterator
    {
        return new ConfigurationDimensionsIterator($class, $iterator, $id);
    }
}
