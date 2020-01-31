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

namespace Viserio\Component\Config\Tests\Unit\Traits;

use ArrayIterator;
use IteratorIterator;
use stdClass;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentContainerIdConfiguration;
use Viserio\Component\Config\Tests\Fixture\FlexibleComponentConfiguration;
use Viserio\Component\Config\Tests\Fixture\PlainComponentConfiguration;
use Viserio\Contract\Config\Exception\DimensionNotFoundException;
use Viserio\Contract\Config\Exception\InvalidArgumentException;
use Viserio\Contract\Config\Exception\UnexpectedValueException;
use Viserio\Contract\Config\RequiresComponentConfig as RequiresComponentConfigContract;

/**
 * @method IteratorIterator getConfigurationIterator(string $class, \ArrayIterator $iterator, ?string $id = null)
 */
trait ConfigurationDimensionsIteratorTestTrait
{
    public function testDimensionsResolving(): void
    {
        $expected = ['test' => true];
        $arrayIterator = new ArrayIterator(['viserio' => ['foo' => $expected]]);

        $class = new class() implements RequiresComponentConfigContract {
            /**
             * {@inheritdoc}
             */
            public static function getDimensions(): iterable
            {
                return [
                    'viserio',
                    'foo',
                ];
            }
        };

        $iterator = $this->getConfigurationIterator(\get_class($class), $arrayIterator);

        $array = [];

        foreach ($iterator as $key => $value) {
            $array[$key] = $value;
        }

        self::assertSame($expected, $array);
    }

    /**
     * @dataProvider provideConfigurationDimensionsIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testDimensionsResolvingShouldReturnConfigurationWithContainerId($config): void
    {
        $iterator = $this->getConfigurationIterator(
            ConnectionComponentContainerIdConfiguration::class,
            new ArrayIterator($config),
            'orm_default'
        );

        $array = \iterator_to_array($iterator);

        self::assertArrayHasKey('driverClass', $array);
        self::assertArrayHasKey('params', $array);
    }

    /**
     * @dataProvider provideConfigurationDimensionsIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testDimensionsResolvingShouldReturnConfiguration($config): void
    {
        $iterator = $this->getConfigurationIterator(
            ConnectionComponentConfiguration::class,
            new ArrayIterator($config)
        );

        $array = \iterator_to_array($iterator);

        self::assertArrayHasKey('orm_default', $array);
    }

    public function testDimensionsResolvingShouldThrowAUnexpectedValueExceptionIfConfigIsNotAnArray(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Configuration must either be of type [array] or implement [\\ArrayAccess]. Configuration position is [doctrine].');

        $this->getConfigurationIterator(ConnectionComponentConfiguration::class, new ArrayIterator(['doctrine' => new stdClass()]));
    }

    public function testDimensionsResolvingShouldThrowAInvalidArgumentExceptionIfConfigIdIsProvidedButRequiresConfigIdIsNotImplemented(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The class [Viserio\\Component\\Config\\Tests\\Fixture\\ConnectionComponentConfiguration] does not support multiple instances.');

        $this->getConfigurationIterator(ConnectionComponentConfiguration::class, new ArrayIterator(['doctrine' => []]), 'configId');
    }

    /**
     * @dataProvider provideConfigurationDimensionsIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testDimensionsResolvingShouldThrowADimensionNotFoundExceptionIfConfigIdIsMissingWithRequiresConfigId(
        $config
    ): void {
        $this->expectException(DimensionNotFoundException::class);
        $this->expectExceptionMessage('The configuration [["doctrine" => ["connection"]]] needs a config id in class [Viserio\\Component\\Config\\Tests\\Fixture\\ConnectionComponentContainerIdConfiguration].');

        $this->getConfigurationIterator(ConnectionComponentContainerIdConfiguration::class, new ArrayIterator($config));
    }

    public function testDimensionsResolvingShouldThrowADimensionNotFoundExceptionIfNoVendorConfigIsAvailable(): void
    {
        $this->expectException(DimensionNotFoundException::class);
        $this->expectExceptionMessage('No dimension configuration was set or found for [["doctrine" => ["connection"]]] in class [Viserio\\Component\\Config\\Tests\\Fixture\\ConnectionComponentConfiguration].');

        $this->getConfigurationIterator(ConnectionComponentConfiguration::class, new ArrayIterator(['doctrine' => []]));
    }

    public function testDimensionsResolvingShouldThrowADimensionNotFoundExceptionIfNoPackageConfigIsAvailable(): void
    {
        $this->expectException(DimensionNotFoundException::class);
        $this->expectExceptionMessage('No dimension configuration was set or found for [["doctrine" => ["connection"]]] in class [Viserio\\Component\\Config\\Tests\\Fixture\\ConnectionComponentConfiguration].');

        $this->getConfigurationIterator(ConnectionComponentConfiguration::class, new ArrayIterator(['doctrine' => ['connection' => null]]));
    }

    public function testDimensionsResolvingShouldThrowADimensionNotFoundExceptionIfNoContainerIdIsAvailable(): void
    {
        $this->expectException(DimensionNotFoundException::class);
        $this->expectExceptionMessage('No dimension configuration was set or found for [["doctrine" => ["connection" => ["orm_default"]]]] in class [Viserio\\Component\\Config\\Tests\\Fixture\\ConnectionComponentContainerIdConfiguration].');

        $this->getConfigurationIterator(
            ConnectionComponentContainerIdConfiguration::class,
            new ArrayIterator(['doctrine' => ['connection' => ['orm_default' => null]]]),
            'orm_default'
        );
    }

    public function testDimensionsResolvingShouldThrowADimensionNotFoundExceptionIfDimensionIsNotAvailable(): void
    {
        $this->expectException(DimensionNotFoundException::class);
        $this->expectExceptionMessage('No dimension configuration was set or found for [["one" => ["two" => ["three" => ["four"]]]]] in class [Viserio\\Component\\Config\\Tests\\Fixture\\FlexibleComponentConfiguration].');

        $this->getConfigurationIterator(
            FlexibleComponentConfiguration::class,
            new ArrayIterator(['one' => ['two' => ['three' => ['invalid' => ['dimension']]]]])
        );
    }

    public function testDimensionsResolvingShouldThrowAUnexpectedValueExceptionIfRetrievedConfigIsNotAnArrayOrArrayAccess(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Configuration must either be of type [array] or implement [\\ArrayAccess]. Configuration position is [doctrine.connection].');

        $this->getConfigurationIterator(
            ConnectionComponentContainerIdConfiguration::class,
            new ArrayIterator(['doctrine' => ['connection' => ['orm_default' => new stdClass()]]]),
            'orm_default'
        );
    }

    /**
     * @dataProvider provideConfigurationDimensionsIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testDimensionsResolvingReturnsDataWithFlexibleDimensions($config): void
    {
        $iterator = $this->getConfigurationIterator(
            FlexibleComponentConfiguration::class,
            new ArrayIterator($config)
        );

        $config = \iterator_to_array($iterator);

        self::assertArrayHasKey('name', $config);
        self::assertArrayHasKey('class', $config);
    }

    /**
     * @dataProvider provideConfigurationDimensionsIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testDimensionsResolvingReturnsDataWithNoDimensions($config): void
    {
        $iterator = $this->getConfigurationIterator(
            PlainComponentConfiguration::class,
            new ArrayIterator($config)
        );

        $config = \iterator_to_array($iterator);

        self::assertArrayHasKey('doctrine', $config);
        self::assertArrayHasKey('one', $config);
    }

    public static function provideConfigurationDimensionsIteratorConfigCases(): iterable
    {
        $config = require dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'testing.config.php';

        return [
            [
                $config,
            ],
        ];
    }
}
