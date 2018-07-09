<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests;

use ArrayIterator;
use ArrayObject;
use Doctrine\DBAL\Driver\PDOMySql\Driver;
use Exception;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use Viserio\Component\Contract\Config\Repository as RepositoryContract;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsMandatoryContainetIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionMandatoryConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionMandatoryContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionMandatoryRecursiveContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\FlexibleConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\InvalidValidatedConfigurationFixture;
use Viserio\Component\OptionsResolver\Tests\Fixture\OptionsResolver;
use Viserio\Component\OptionsResolver\Tests\Fixture\PackageDefaultAndMandatoryOptionsConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\PackageDefaultOptionsConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\PlainConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\UniversalContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ValidatedConfigurationFixture;
use  Viserio\Component\OptionsResolver\Tests\Fixture\ValidatedDimensionalConfigurationFixture;
use Viserio\Component\OptionsResolver\Tests\Fixture\ValidateDefaultValueOnOverwriteFixture;

/**
 * Code in this test is taken from interop-config.
 *
 * @author Sandro Keil https://sandro-keil.de/blog/
 * @copyright Copyright (c) 2015-2017 Sandro Keil
 *
 * @internal
 */
final class OptionsResolverTest extends MockeryTestCase
{
    public function testOptionsResolverThrowsUnexpectedValueExceptionIfConfigIsNotAnArray(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\UnexpectedValueException::class);
        $this->expectExceptionMessage('Configuration must either be of type [array] or implement [\\ArrayAccess]. Configuration position is [doctrine].');

        $this->getOptionsResolver(new ConnectionConfiguration(), ['doctrine' => new stdClass()]);
    }

    public function testOptionsThrowsInvalidArgumentExIfConfigIdIsProvidedButRequiresConfigIdIsNotImplemented(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The factory [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionConfiguration] does not support multiple instances.');

        $this->getOptionsResolver(new ConnectionConfiguration(), ['doctrine' => []], 'configId');
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsThrowsOptionNotFoundExceptionIfConfigIdIsMissingWithRequiresConfigId($config): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException::class);
        $this->expectExceptionMessage('The configuration [doctrine.connection] needs a config id in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionContainerIdConfiguration].');

        $this->getOptionsResolver(new ConnectionContainerIdConfiguration(), $config);
    }

    public function testOptionsThrowsOptionNotFoundExceptionIfNoVendorConfigIsAvailable(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [doctrine.connection] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionConfiguration].');

        $this->getOptionsResolver(new ConnectionConfiguration(), ['doctrine' => []]);
    }

    public function testOptionsThrowsOptionNotFoundExceptionIfNoPackageOptionIsAvailable(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [doctrine.connection] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionConfiguration].');

        $this->getOptionsResolver(new ConnectionConfiguration(), ['doctrine' => ['connection' => null]]);
    }

    public function testOptionsThrowsOptionNotFoundExceptionIfNoContainerIdOptionIsAvailable(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [doctrine.connection.orm_default] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionContainerIdConfiguration].');

        $this->getOptionsResolver(
            new ConnectionContainerIdConfiguration(),
            ['doctrine' => ['connection' => ['orm_default' => null]]],
            'orm_default'
        );
    }

    public function testOptionsThrowsOptionNotFoundExceptionIfDimensionIsNotAvailable(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [one.two.three.four] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\FlexibleConfiguration].');

        $this->getOptionsResolver(
            new FlexibleConfiguration(),
            ['one' => ['two' => ['three' => ['invalid' => ['dimension']]]]]
        );
    }

    public function testOptionsThrowsExceptionIfMandatoryOptionsWithDefaultOptionsSetAndNoConfigurationIsSet(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [vendor] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\PackageDefaultAndMandatoryOptionsConfiguration].');

        $this->getOptionsResolver(
            new PackageDefaultAndMandatoryOptionsConfiguration(),
            []
        );
    }

    public function testOptionsThrowsUnexpectedValueExceptionIfRetrievedOptionsNotAnArrayOrArrayAccess(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\UnexpectedValueException::class);
        $this->expectExceptionMessage('Configuration must either be of type [array] or implement [\\ArrayAccess]. Configuration position is [doctrine.connection].');

        $this->getOptionsResolver(
            new ConnectionContainerIdConfiguration(),
            ['doctrine' => ['connection' => ['orm_default' => new \stdClass()]]],
            'orm_default'
        );
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithContainerId($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        static::assertArrayHasKey('driverClass', $options);
        static::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsReturnsData($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionConfiguration(),
            $config
        );

        static::assertArrayHasKey('orm_default', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithFlexibleDimensions($config): void
    {
        $options = $this->getOptionsResolver(
            new FlexibleConfiguration(),
            $config
        );

        static::assertArrayHasKey('name', $options);
        static::assertArrayHasKey('class', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithNoDimensions($config): void
    {
        $options = $this->getOptionsResolver(
            new PlainConfiguration(),
            $config
        );

        static::assertArrayHasKey('doctrine', $options);
        static::assertArrayHasKey('one', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithDefaultOptions($config): void
    {
        $stub = new ConnectionDefaultOptionsMandatoryContainetIdConfiguration();

        unset($config['doctrine']['connection']['orm_default']['params']['host'], $config['doctrine']['connection']['orm_default']['params']['port']);

        $options = $options = $this->getOptionsResolver(
            $stub,
            $config,
            'orm_default'
        );
        $defaultOptions = ConnectionDefaultOptionsMandatoryContainetIdConfiguration::getDefaultOptions();

        static::assertCount(2, $options);
        static::assertArrayHasKey('params', $options);
        static::assertSame($options['params']['host'], $defaultOptions['params']['host']);
        static::assertSame($options['params']['port'], $defaultOptions['params']['port']);
        static::assertSame(
            $options['params']['user'],
            $config['doctrine']['connection']['orm_default']['params']['user']
        );

        $config = $this->getTestConfig();

        // remove main index key
        unset($config['doctrine']['connection']['orm_default']['params']);

        $options = $options = $this->getOptionsResolver(
            $stub,
            $config,
            'orm_default'
        );

        static::assertCount(2, $options);
        static::assertArrayHasKey('params', $options);
        static::assertSame($options['params']['host'], $defaultOptions['params']['host']);
        static::assertSame($options['params']['port'], $defaultOptions['params']['port']);
    }

    public function testOptionsReturnsPackageDataWithDefaultOptionsIfNoConfigurationIsSet(): void
    {
        $expected = [
            'minLength' => 2,
            'maxLength' => 10,
        ];
        $options = $this->getOptionsResolver(
            new PackageDefaultOptionsConfiguration(),
            []
        );

        static::assertCount(2, $options);
        static::assertSame($expected, $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsThatDefaultOptionsNotOverrideProvidedOptions($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionDefaultOptionsMandatoryContainetIdConfiguration(),
            $config,
            'orm_default'
        );

        static::assertCount(2, $options);
        static::assertArrayHasKey('params', $options);
        static::assertSame(
            $options['params']['host'],
            $config['doctrine']['connection']['orm_default']['params']['host']
        );
        static::assertSame(
            $options['params']['port'],
            $config['doctrine']['connection']['orm_default']['params']['port']
        );
        static::assertSame(
            $options['params']['user'],
            $config['doctrine']['connection']['orm_default']['params']['user']
        );
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsChecksMandatoryOptions($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionMandatoryConfiguration(),
            $config
        );

        static::assertCount(1, $options);
        static::assertArrayHasKey('orm_default', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsChecksMandatoryOptionsWithContainerId($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionMandatoryContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        static::assertCount(2, $options);
        static::assertArrayHasKey('driverClass', $options);
        static::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsThrowsMandatoryOptionNotFoundExceptionIfMandatoryOptionIsMissing($config): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\MandatoryOptionNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [params] was not set for configuration [doctrine.connection].');

        unset($config['doctrine']['connection']['orm_default']['params']);

        $this->getOptionsResolver(
            new ConnectionMandatoryContainerIdConfiguration(),
            $config,
            'orm_default'
        );
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsThrowsMandatoryOptionNotFoundExceptionIfMandatoryOptionRecursiveIsMissing($config): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\MandatoryOptionNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [dbname] was not set for configuration [doctrine.connection].');

        unset($config['doctrine']['connection']['orm_default']['params']['dbname']);

        $this->getOptionsResolver(
            new ConnectionMandatoryRecursiveContainerIdConfiguration(),
            $config,
            'orm_default'
        );
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsWithRecursiveMandatoryOptionCheck($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionMandatoryRecursiveContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        static::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsWithRecursiveArrayIteratorMandatoryOptionCheck($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        static::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider configDataProvider
     */
    public function testOptionsThrowsMandatoryOptionNotFoundExceptionIfOptionsAreEmpty(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\MandatoryOptionNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [params] was not set for configuration [doctrine.connection].');

        $this->getOptionsResolver(
            new ConnectionMandatoryRecursiveContainerIdConfiguration(),
            ['doctrine' => ['connection' => ['orm_default' => []]]],
            'orm_default'
        );
    }

    public function testEmptyArrayAccessWithDefaultOptions(): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionDefaultOptionsConfiguration(),
            new ArrayIterator([])
        );

        static::assertCount(1, $options);
        static::assertArrayHasKey('params', $options);
        static::assertSame(
            $options['params']['host'],
            'awesomehost'
        );
        static::assertSame(
            $options['params']['port'],
            '4444'
        );
    }

    /**
     * @dataProvider configObjectsDataProvider
     *
     * @param mixed $config
     * @param mixed $type
     */
    public function testOptionsWithObjects($config, $type): void
    {
        $options = $this->getOptionsResolver(
            new UniversalContainerIdConfiguration($type),
            $config,
            'orm_default'
        );

        static::assertCount(2, $options);
        static::assertArrayHasKey('driverClass', $options);
        static::assertArrayHasKey('params', $options);

        $driverClass = Driver::class;
        $host        = 'localhost';
        $dbname      = 'database';
        $user        = 'username';
        $password    = 'password';
        $port        = '4444';

        if ($type !== UniversalContainerIdConfiguration::TYPE_ONLY_ITERATOR) {
            $driverClass = $config['doctrine']['connection']['orm_default']['driverClass'];
            $host        = $config['doctrine']['connection']['orm_default']['params']['host'];
            $dbname      = $config['doctrine']['connection']['orm_default']['params']['dbname'];
            $user        = $config['doctrine']['connection']['orm_default']['params']['user'];
            $password    = $config['doctrine']['connection']['orm_default']['params']['password'];
        }

        static::assertSame($options['driverClass'], $driverClass);
        static::assertSame($options['params']['host'], $host);
        static::assertSame($options['params']['port'], $port);
        static::assertSame($options['params']['dbname'], $dbname);
        static::assertSame($options['params']['user'], $user);
        static::assertSame($options['params']['password'], $password);
    }

    public function testDataResolverWithRepositoryContract(): void
    {
        $defaultConfig = [
            // package name
            'connection' => [
                // container id
                'orm_default' => [
                    // mandatory params
                    'driverClass' => Driver::class,
                    'params'      => [
                        'host'     => 'localhost',
                        'port'     => '3306',
                        'user'     => 'username',
                        'password' => 'password',
                        'dbname'   => 'database',
                    ],
                ],
            ],
        ];
        $container = $this->mock(ContainerInterface::class);
        $config    = $this->mock(RepositoryContract::class);
        $config->shouldReceive('offsetExists')
            ->once()
            ->with('doctrine')
            ->andReturn(true);
        $config->shouldReceive('offsetGet')
            ->once()
            ->with('doctrine')
            ->andReturn($defaultConfig);
        $container->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with(RepositoryContract::class)
            ->andReturn($config);

        $options = $this->getOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            $container,
            'orm_default'
        );

        static::assertArrayHasKey('params', $options);
    }

    public function testDataResolverWithRepository(): void
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(false);
        $container->shouldReceive('has')
            ->with('config')
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with('config')
            ->andReturn($this->getTestConfig());

        $options = $this->getOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            $container,
            'orm_default'
        );

        static::assertArrayHasKey('params', $options);
    }

    public function testDataResolverWithOptions(): void
    {
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('has')
            ->with(RepositoryContract::class)
            ->andReturn(false);
        $container->shouldReceive('has')
            ->with('config')
            ->andReturn(false);
        $container->shouldReceive('has')
            ->with('options')
            ->andReturn(true);
        $container->shouldReceive('get')
            ->with('options')
            ->andReturn($this->getTestConfig());

        $options = $this->getOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            $container,
            'orm_default'
        );

        static::assertArrayHasKey('params', $options);
    }

    public function testDataResolverThrowException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No configuration found.');

        $this->getOptionsResolver(
            new ConnectionMandatoryRecursiveArrayIteratorContainerIdConfiguration(),
            new stdClass()
        );
    }

    public function testValidatorThrowExceptionOnConfig(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getOptionsResolver(
            new ValidatedConfigurationFixture(),
            [
                'vendor' => [
                    'package' => [
                        'maxLength' => 'string',
                    ],
                ],
            ]
        );
    }

    public function testValidatorOnConfig(): void
    {
        try {
            $this->getOptionsResolver(
                new ValidatedConfigurationFixture(),
                [
                    'vendor' => [
                        'package' => [
                            'maxLength' => 2,
                        ],
                    ],
                ]
            );
        } catch (Exception $e) {
            static::assertFalse(true);
        }

        static::assertTrue(true);
    }

    public function testValidatorThrowExceptionOnDimensionalConfig(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getOptionsResolver(
            new ValidatedDimensionalConfigurationFixture(),
            [
                'vendor' => [
                    'package' => [
                        'foo' => [
                            'maxLength' => 'string',
                        ],
                    ],
                ],
            ]
        );
    }

    public function testValidatorOnDimensionalConfig(): void
    {
        try {
            $this->getOptionsResolver(
                new ValidatedDimensionalConfigurationFixture(),
                [
                    'vendor' => [
                        'package' => [
                            'maxLength' => 'string',
                            'foo'       => [
                                'maxLength' => 1,
                            ],
                        ],
                    ],
                ]
            );
        } catch (Exception $e) {
            static::assertFalse(true);
        }

        static::assertTrue(true);
    }

    public function testThrowExceptionOnInvalidValidator(): void
    {
        $this->expectException(\Viserio\Component\Contract\OptionsResolver\Exception\InvalidValidatorException::class);
        $this->expectExceptionMessage('he validator must be of type callable, [string] given, in Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\InvalidValidatedConfigurationFixture.');

        $this->getOptionsResolver(
            new InvalidValidatedConfigurationFixture(),
            [
                'vendor' => [
                    'package' => [
                        'maxLength' => 'string',
                    ],
                ],
            ]
        );
    }

    public function testValidatorOnDefaultOverwrite(): void
    {
        try {
            $this->getOptionsResolver(
                new ValidateDefaultValueOnOverwriteFixture(),
                [
                    'vendor' => [
                        'package' => [
                            'maxLength' => 20,
                            'minLength' => 10,
                        ],
                    ],
                ]
            );
        } catch (Exception $e) {
            static::assertFalse(true);
        }

        static::assertTrue(true);
    }

    public function testValidatorThrowExceptionOnWrongDefaultValue(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getOptionsResolver(
            new ValidateDefaultValueOnOverwriteFixture(),
            [
                'vendor' => [
                    'package' => [
                        'maxLength' => 20,
                        'minLength' => 'string',
                    ],
                ],
            ]
        );
    }

    public function configDataProvider(): array
    {
        return [
            [$this->getTestConfig()],
            [new ArrayObject($this->getTestConfig())],
            [new ArrayIterator($this->getTestConfig())],
        ];
    }

    public function configObjectsDataProvider(): array
    {
        return [
            [$this->getTestConfig(), UniversalContainerIdConfiguration::TYPE_ARRAY_ARRAY],
            [new ArrayObject($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_ARRAY],
            [new ArrayIterator($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_ARRAY],
            [$this->getTestConfig(), UniversalContainerIdConfiguration::TYPE_ARRAY_OBJECT],
            [new ArrayObject($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_OBJECT],
            [new ArrayIterator($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_OBJECT],
            [$this->getTestConfig(), UniversalContainerIdConfiguration::TYPE_ARRAY_ITERATOR],
            [new ArrayObject($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_ITERATOR],
            [new ArrayIterator($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ARRAY_ITERATOR],
            [$this->getTestConfig(), UniversalContainerIdConfiguration::TYPE_ONLY_ITERATOR],
            [new ArrayObject($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ONLY_ITERATOR],
            [new ArrayIterator($this->getTestConfig()), UniversalContainerIdConfiguration::TYPE_ONLY_ITERATOR],
        ];
    }

    protected function getOptionsResolver($class, $data, string $id = null)
    {
        return (new OptionsResolver())->configure($class, $data)->resolve($id);
    }

    /**
     * Returns test config.
     *
     * @return array
     */
    private function getTestConfig(): array
    {
        return require __DIR__ . '/Fixture/testing.config.php';
    }
}
