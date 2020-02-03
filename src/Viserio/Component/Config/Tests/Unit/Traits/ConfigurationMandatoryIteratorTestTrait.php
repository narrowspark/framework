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
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigMandatoryContainedIdConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentMandatoryConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentMandatoryContainerIdConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentMandatoryRecursiveContainerIdConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultConfigWithMandatoryConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultConfigWithMoreThanOneMandatoryConfiguration;
use Viserio\Contract\Config\Exception\MandatoryConfigNotFoundException;

/**
 * @method IteratorIterator getConfigurationIterator(string $class, ArrayIterator $iterator, ?string $id = null)
 */
trait ConfigurationMandatoryIteratorTestTrait
{
    /**
     * @dataProvider provideConfigurationMandatoryIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testMandatoryResolving($config): void
    {
        $iterator = $this->getConfigurationIterator(
            ConnectionComponentMandatoryConfiguration::class,
            new ArrayIterator($config)
        );

        $array = \iterator_to_array($iterator);

        self::assertCount(1, $array);
        self::assertArrayHasKey('orm_default', $array);
    }

    /**
     * @dataProvider provideConfigurationMandatoryIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testMandatoryResolvingShouldResolveWithId($config): void
    {
        $iterator = $this->getConfigurationIterator(
            ConnectionComponentMandatoryContainerIdConfiguration::class,
            new ArrayIterator($config),
            'orm_default'
        );

        $array = \iterator_to_array($iterator);

        self::assertCount(2, $array);
        self::assertArrayHasKey('driverClass', $array);
        self::assertArrayHasKey('params', $array);
    }

    /**
     * @dataProvider provideConfigurationMandatoryIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testMandatoryResolvingShouldThrowAMandatoryConfigNotFoundExceptionIfMandatoryConfigurationIsMissing(
        $config
    ): void {
        $this->expectException(MandatoryConfigNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [params] was not set for configuration [doctrine.connection].');

        unset($config['doctrine']['connection']['orm_default']['params']);

        $this->getConfigurationIterator(
            ConnectionComponentMandatoryContainerIdConfiguration::class,
            new ArrayIterator($config),
            'orm_default'
        );
    }

    /**
     * @dataProvider provideConfigurationMandatoryIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testMandatoryResolvingShouldThrowAMandatoryConfigNotFoundExceptionIfRecursiveMandatoryConfigurationIsMissing(
        $config
    ): void {
        $this->expectException(MandatoryConfigNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [dbname] was not set for configuration [doctrine.connection].');

        unset($config['doctrine']['connection']['orm_default']['params']['dbname']);

        $this->getConfigurationIterator(
            ConnectionComponentMandatoryRecursiveContainerIdConfiguration::class,
            new ArrayIterator($config),
            'orm_default'
        );
    }

    /**
     * @dataProvider provideConfigurationMandatoryIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testMandatoryResolvingShouldResolveRecursiveMandatoryConfiguration($config): void
    {
        $iterator = $this->getConfigurationIterator(
            ConnectionComponentMandatoryRecursiveContainerIdConfiguration::class,
            new ArrayIterator($config),
            'orm_default'
        );

        $array = \iterator_to_array($iterator);

        self::assertArrayHasKey('params', $array);
    }

    /**
     * @dataProvider provideConfigurationMandatoryIteratorConfigCases
     */
    public function testMandatoryResolvingShouldThrowAMandatoryConfigNotFoundExceptionIfConfigurationIsEmpty(): void
    {
        $this->expectException(MandatoryConfigNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [params] was not set for configuration [doctrine.connection].');

        $this->getConfigurationIterator(
            ConnectionComponentMandatoryRecursiveContainerIdConfiguration::class,
            new ArrayIterator(['doctrine' => ['connection' => ['orm_default' => []]]]),
            'orm_default'
        );
    }

    public function testMandatoryConfigResolvingShouldWorkWithMandatoryConfiguration(): void
    {
        $this->expectException(MandatoryConfigNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [driverClass] was not set for configuration [].');

        $this->getConfigurationIterator(
            ConnectionDefaultConfigWithMandatoryConfiguration::class,
            new ArrayIterator([])
        );
    }

    public function testMandatoryConfigResolvingShouldSupportMoreThanOneMandatoryConfiguration(): void
    {
        $this->expectException(MandatoryConfigNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [connection] was not set for configuration [].');

        $this->getConfigurationIterator(
            ConnectionDefaultConfigWithMoreThanOneMandatoryConfiguration::class,
            new ArrayIterator(['driverClass' => 'foo'])
        );
    }

    /**
     * @dataProvider provideConfigurationMandatoryIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testMandatoryConfigResolvingShouldReturnDataWithProvidedDefaultConfigPart1($config): void
    {
        $defaultOptions = ConnectionComponentDefaultConfigMandatoryContainedIdConfiguration::getDefaultConfig();

        unset($config['doctrine']['connection']['orm_default']['params']['host'], $config['doctrine']['connection']['orm_default']['params']['port']);

        $iterator = $this->getConfigurationIterator(
            ConnectionComponentDefaultConfigMandatoryContainedIdConfiguration::class,
            new ArrayIterator($config),
            'orm_default'
        );

        $array = \iterator_to_array($iterator);

        self::assertCount(2, $array);
        self::assertArrayHasKey('params', $array);
        self::assertSame($array['params']['host'], $defaultOptions['params']['host']);
        self::assertSame($array['params']['port'], $defaultOptions['params']['port']);
        self::assertSame(
            $array['params']['user'],
            $config['doctrine']['connection']['orm_default']['params']['user']
        );
    }

    /**
     * @dataProvider provideConfigurationMandatoryIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testMandatoryConfigResolvingShouldReturnDataWithProvidedDefaultConfigPart2($config): void
    {
        $defaultOptions = ConnectionComponentDefaultConfigMandatoryContainedIdConfiguration::getDefaultConfig();

        // remove main index key
        unset($config['doctrine']['connection']['orm_default']['params']);

        $iterator = $iterator = $this->getConfigurationIterator(
            ConnectionComponentDefaultConfigMandatoryContainedIdConfiguration::class,
            new ArrayIterator($config),
            'orm_default'
        );

        $array = \iterator_to_array($iterator);

        self::assertCount(2, $array);
        self::assertArrayHasKey('params', $array);
        self::assertSame($array['params']['host'], $defaultOptions['params']['host']);
        self::assertSame($array['params']['port'], $defaultOptions['params']['port']);
    }

    /**
     * @dataProvider provideConfigurationMandatoryIteratorConfigCases
     *
     * @param mixed $config
     */
    public function testMandatoryConfigResolvingShouldNotOverwriteProvidedConfig($config): void
    {
        $iterator = $this->getConfigurationIterator(
            ConnectionComponentDefaultConfigMandatoryContainedIdConfiguration::class,
            new ArrayIterator($config),
            'orm_default'
        );

        $array = \iterator_to_array($iterator);

        self::assertCount(2, $array);
        self::assertArrayHasKey('params', $array);
        self::assertSame(
            $array['params']['host'],
            $config['doctrine']['connection']['orm_default']['params']['host']
        );
        self::assertSame(
            $array['params']['port'],
            $config['doctrine']['connection']['orm_default']['params']['port']
        );
        self::assertSame(
            $array['params']['user'],
            $config['doctrine']['connection']['orm_default']['params']['user']
        );
    }

    public static function provideConfigurationMandatoryIteratorConfigCases(): iterable
    {
        $config = require dirname(__DIR__, 2) . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'testing.config.php';

        return [
            [
                $config,
            ],
        ];
    }
}
