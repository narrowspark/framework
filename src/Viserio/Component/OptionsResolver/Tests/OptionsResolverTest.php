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

namespace Viserio\Component\OptionsResolver\Tests;

use ArrayIterator;
use Exception;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use RuntimeException;
use stdClass;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionMandatoryContainedIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionMandatoryContainedIdWithDeprecationKeyConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionsWithMandatoryConfigurationAndStringValidator;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionWithDeprecationKeyAndEmptyMessageConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionWithDeprecationKeyAndInvalidMessageConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionWithDeprecationKeyAndMessageConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionWithDeprecationKeyConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionWithMultiDimensionalDeprecationKeyConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentMandatoryConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentMandatoryContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentMandatoryRecursiveArrayContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentMandatoryRecursiveContainerIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentWithNotFoundDeprecationKeyConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndStringValidator;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayAndTwoValidator;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayValidator;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoValidator;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndValidator;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryNullValueConfigurationAndStringValidator;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionWithMandatoryConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionWithMoreThanOneMandatoryConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\DontValidatedDefaultConfigurationFixture;
use Viserio\Component\OptionsResolver\Tests\Fixture\FlexibleComponentConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\InvalidValidatedComponentConfigurationFixture;
use Viserio\Component\OptionsResolver\Tests\Fixture\OptionsResolver;
use Viserio\Component\OptionsResolver\Tests\Fixture\PackageDefaultAndMandatoryOptionComponentConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\PackageDefaultOptionComponentConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\PlainComponentConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ValidatedComponentConfigurationFixture;
use Viserio\Component\OptionsResolver\Tests\Fixture\ValidatedComponentWithArrayValidatorConfigurationFixture;
use Viserio\Component\OptionsResolver\Tests\Fixture\ValidatedDimensionalComponentConfigurationFixture;
use Viserio\Component\OptionsResolver\Tests\Fixture\ValidateDefaultValueOnOverwriteComponentFixture;
use Viserio\Contract\OptionsResolver\Exception\InvalidArgumentException;
use Viserio\Contract\OptionsResolver\Exception\InvalidValidatorException;
use Viserio\Contract\OptionsResolver\Exception\MandatoryOptionNotFoundException;
use Viserio\Contract\OptionsResolver\Exception\OptionNotFoundException;
use Viserio\Contract\OptionsResolver\Exception\UnexpectedValueException;

/**
 * Code in this test is taken from interop-config.
 *
 * @author Sandro Keil https://sandro-keil.de/blog/
 * @copyright Copyright (c) 2015-2017 Sandro Keil
 *
 * @internal
 *
 * @small
 */
final class OptionsResolverTest extends MockeryTestCase
{
    public function testOptionsResolverThrowsUnexpectedValueExceptionIfConfigIsNotAnArray(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Configuration must either be of type [array] or implement [\\ArrayAccess]. Configuration position is [doctrine].');

        $this->getOptionsResolver(new ConnectionComponentConfiguration(), ['doctrine' => new stdClass()]);
    }

    public function testOptionsThrowsInvalidArgumentExIfConfigIdIsProvidedButRequiresConfigIdIsNotImplemented(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The factory [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionComponentConfiguration] does not support multiple instances.');

        $this->getOptionsResolver(new ConnectionComponentConfiguration(), ['doctrine' => []], 'configId');
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsThrowsOptionNotFoundExceptionIfConfigIdIsMissingWithRequiresConfigId($config): void
    {
        $this->expectException(OptionNotFoundException::class);
        $this->expectExceptionMessage('The configuration [["doctrine" => ["connection"]]] needs a config id in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionComponentContainerIdConfiguration].');

        $this->getOptionsResolver(new ConnectionComponentContainerIdConfiguration(), $config);
    }

    public function testOptionsThrowsOptionNotFoundExceptionIfNoVendorConfigIsAvailable(): void
    {
        $this->expectException(OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [["doctrine" => ["connection"]]] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionComponentConfiguration].');

        $this->getOptionsResolver(new ConnectionComponentConfiguration(), ['doctrine' => []]);
    }

    public function testOptionsThrowsOptionNotFoundExceptionIfNoPackageOptionIsAvailable(): void
    {
        $this->expectException(OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [["doctrine" => ["connection"]]] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionComponentConfiguration].');

        $this->getOptionsResolver(new ConnectionComponentConfiguration(), ['doctrine' => ['connection' => null]]);
    }

    public function testOptionsThrowsOptionNotFoundExceptionIfNoContainerIdOptionIsAvailable(): void
    {
        $this->expectException(OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [["doctrine" => ["connection" => ["orm_default"]]]] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\ConnectionComponentContainerIdConfiguration].');

        $this->getOptionsResolver(
            new ConnectionComponentContainerIdConfiguration(),
            ['doctrine' => ['connection' => ['orm_default' => null]]],
            'orm_default'
        );
    }

    public function testOptionsThrowsOptionNotFoundExceptionIfDimensionIsNotAvailable(): void
    {
        $this->expectException(OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [["one" => ["two" => ["three" => ["four"]]]]] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\FlexibleComponentConfiguration].');

        $this->getOptionsResolver(
            new FlexibleComponentConfiguration(),
            ['one' => ['two' => ['three' => ['invalid' => ['dimension']]]]]
        );
    }

    public function testOptionsThrowsExceptionIfMandatoryOptionsWithDefaultOptionsSetAndNoConfigurationIsSet(): void
    {
        $this->expectException(OptionNotFoundException::class);
        $this->expectExceptionMessage('No options set for configuration [["vendor"]] in class [Viserio\\Component\\OptionsResolver\\Tests\\Fixture\\PackageDefaultAndMandatoryOptionComponentConfiguration].');

        $this->getOptionsResolver(
            new PackageDefaultAndMandatoryOptionComponentConfiguration(),
            []
        );
    }

    public function testOptionsThrowsUnexpectedValueExceptionIfRetrievedOptionsNotAnArrayOrArrayAccess(): void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Configuration must either be of type [array] or implement [\\ArrayAccess]. Configuration position is [doctrine.connection].');

        $this->getOptionsResolver(
            new ConnectionComponentContainerIdConfiguration(),
            ['doctrine' => ['connection' => ['orm_default' => new stdClass()]]],
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
            new ConnectionComponentContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertArrayHasKey('driverClass', $options);
        self::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsReturnsData($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionComponentConfiguration(),
            $config
        );

        self::assertArrayHasKey('orm_default', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithFlexibleDimensions($config): void
    {
        $options = $this->getOptionsResolver(
            new FlexibleComponentConfiguration(),
            $config
        );

        self::assertArrayHasKey('name', $options);
        self::assertArrayHasKey('class', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithNoDimensions($config): void
    {
        $options = $this->getOptionsResolver(
            new PlainComponentConfiguration(),
            $config
        );

        self::assertArrayHasKey('doctrine', $options);
        self::assertArrayHasKey('one', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsReturnsDataWithDefaultOptions($config): void
    {
        $stub = new ConnectionComponentDefaultOptionMandatoryContainedIdConfiguration();

        unset($config['doctrine']['connection']['orm_default']['params']['host'], $config['doctrine']['connection']['orm_default']['params']['port']);

        $options = $options = $this->getOptionsResolver(
            $stub,
            $config,
            'orm_default'
        );
        $defaultOptions = ConnectionComponentDefaultOptionMandatoryContainedIdConfiguration::getDefaultOptions();

        self::assertCount(2, $options);
        self::assertArrayHasKey('params', $options);
        self::assertSame($options['params']['host'], $defaultOptions['params']['host']);
        self::assertSame($options['params']['port'], $defaultOptions['params']['port']);
        self::assertSame(
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

        self::assertCount(2, $options);
        self::assertArrayHasKey('params', $options);
        self::assertSame($options['params']['host'], $defaultOptions['params']['host']);
        self::assertSame($options['params']['port'], $defaultOptions['params']['port']);
    }

    public function testOptionsReturnsPackageDataWithDefaultOptionsIfNoConfigurationIsSet(): void
    {
        $expected = [
            'minLength' => 2,
            'maxLength' => 10,
        ];
        $options = $this->getOptionsResolver(
            new PackageDefaultOptionComponentConfiguration(),
            []
        );

        self::assertCount(2, $options);
        self::assertSame($expected, $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsThatDefaultOptionsNotOverrideProvidedOptions($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionComponentDefaultOptionMandatoryContainedIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertCount(2, $options);
        self::assertArrayHasKey('params', $options);
        self::assertSame(
            $options['params']['host'],
            $config['doctrine']['connection']['orm_default']['params']['host']
        );
        self::assertSame(
            $options['params']['port'],
            $config['doctrine']['connection']['orm_default']['params']['port']
        );
        self::assertSame(
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
            new ConnectionComponentMandatoryConfiguration(),
            $config
        );

        self::assertCount(1, $options);
        self::assertArrayHasKey('orm_default', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsChecksMandatoryOptionsWithContainerId($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionComponentMandatoryContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertCount(2, $options);
        self::assertArrayHasKey('driverClass', $options);
        self::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsThrowsMandatoryOptionNotFoundExceptionIfMandatoryOptionIsMissing($config): void
    {
        $this->expectException(MandatoryOptionNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [params] was not set for configuration [doctrine.connection].');

        unset($config['doctrine']['connection']['orm_default']['params']);

        $this->getOptionsResolver(
            new ConnectionComponentMandatoryContainerIdConfiguration(),
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
        $this->expectException(MandatoryOptionNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [dbname] was not set for configuration [doctrine.connection].');

        unset($config['doctrine']['connection']['orm_default']['params']['dbname']);

        $this->getOptionsResolver(
            new ConnectionComponentMandatoryRecursiveContainerIdConfiguration(),
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
            new ConnectionComponentMandatoryRecursiveContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider configDataProvider
     *
     * @param mixed $config
     */
    public function testOptionsWithRecursiveArrayIteratorMandatoryOptionCheck($config): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionComponentMandatoryRecursiveArrayContainerIdConfiguration(),
            $config,
            'orm_default'
        );

        self::assertArrayHasKey('params', $options);
    }

    /**
     * @dataProvider configDataProvider
     */
    public function testOptionsThrowsMandatoryOptionNotFoundExceptionIfOptionsAreEmpty(): void
    {
        $this->expectException(MandatoryOptionNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [params] was not set for configuration [doctrine.connection].');

        $this->getOptionsResolver(
            new ConnectionComponentMandatoryRecursiveContainerIdConfiguration(),
            ['doctrine' => ['connection' => ['orm_default' => []]]],
            'orm_default'
        );
    }

    public function testEmptyArrayAccessWithDefaultOptions(): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionComponentDefaultOptionConfiguration(),
            new ArrayIterator([])
        );

        self::assertCount(1, $options);
        self::assertArrayHasKey('params', $options);
        self::assertSame(
            $options['params']['host'],
            'awesomehost'
        );
        self::assertSame(
            $options['params']['port'],
            '4444'
        );
    }

    public function testValidatorThrowExceptionOnConfig(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getOptionsResolver(
            new ValidatedComponentConfigurationFixture(),
            [
                'vendor' => [
                    'package' => [
                        'maxLength' => 'string',
                    ],
                ],
            ]
        );
    }

    public function testArrayValidatorsThrowOneExceptionOnConfig(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [maxLength]; Expected [string] or [int], but got [boolean], in [Viserio\Component\OptionsResolver\Tests\Fixture\ValidatedComponentWithArrayValidatorConfigurationFixture].');

        $this->getOptionsResolver(
            new ValidatedComponentWithArrayValidatorConfigurationFixture(),
            [
                'vendor' => [
                    'package' => [
                        'maxLength' => true,
                    ],
                ],
            ]
        );
    }

    public function testValidatorOnConfig(): void
    {
        try {
            $this->getOptionsResolver(
                new ValidatedComponentConfigurationFixture(),
                [
                    'vendor' => [
                        'package' => [
                            'maxLength' => 2,
                        ],
                    ],
                ]
            );

            $this->expectNotToPerformAssertions();
        } catch (Exception $e) {
            self::fail($e->getMessage());
        }
    }

    public function testValidatorThrowExceptionOnDimensionalConfig(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getOptionsResolver(
            new ValidatedDimensionalComponentConfigurationFixture(),
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
                new ValidatedDimensionalComponentConfigurationFixture(),
                [
                    'vendor' => [
                        'package' => [
                            'maxLength' => 'string',
                            'foo' => [
                                'maxLength' => 1,
                            ],
                        ],
                    ],
                ]
            );

            $this->expectNotToPerformAssertions();
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }

    public function testThrowExceptionOnInvalidValidator(): void
    {
        $this->expectException(InvalidValidatorException::class);
        $this->expectExceptionMessage('The validator must be of type callable or string[]; [string] given, in [Viserio\Component\OptionsResolver\Tests\Fixture\InvalidValidatedComponentConfigurationFixture].');

        $this->getOptionsResolver(
            new InvalidValidatedComponentConfigurationFixture(),
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
                new ValidateDefaultValueOnOverwriteComponentFixture(),
                [
                    'vendor' => [
                        'package' => [
                            'maxLength' => 20,
                            'minLength' => 10,
                        ],
                    ],
                ]
            );

            $this->expectNotToPerformAssertions();
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }

    public function testValidatorThrowExceptionOnWrongDefaultValue(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getOptionsResolver(
            new ValidateDefaultValueOnOverwriteComponentFixture(),
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

    public function testValidatorThrowExceptionOnNullValueIfStringIsRequired(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [driverClass]; Expected [string], but got [NULL], in [Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryNullValueConfigurationAndStringValidator].');

        $this->getOptionsResolver(
            new ConnectionDefaultOptionsWithMandatoryNullValueConfigurationAndStringValidator(),
            [
                'driverClass' => null,
            ]
        );
    }

    public function testConnectionDefaultOptionsConfiguration(): void
    {
        $options = $this->getOptionsResolver(
            new ConnectionDefaultOptionConfiguration(),
            []
        );

        self::assertCount(1, $options);
        self::assertArrayHasKey('params', $options);
        self::assertSame(
            $options['params']['host'],
            'awesomehost'
        );
        self::assertSame(
            $options['params']['port'],
            '4444'
        );
    }

    public function testConnectionDefaultOptionsWithMandatoryConfiguration(): void
    {
        $this->expectException(MandatoryOptionNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [driverClass] was not set for configuration [].');

        $this->getOptionsResolver(
            new ConnectionDefaultOptionWithMandatoryConfiguration(),
            []
        );
    }

    public function testConnectionDefaultOptionsWithMoreThanOneMandatoryConfiguration(): void
    {
        $this->expectException(MandatoryOptionNotFoundException::class);
        $this->expectExceptionMessage('Mandatory option [connection] was not set for configuration [].');

        $this->getOptionsResolver(
            new ConnectionDefaultOptionWithMoreThanOneMandatoryConfiguration(),
            ['driverClass' => 'foo']
        );
    }

    public function testConnectionDefaultOptionsWithMandatoryConfigurationAndValidator(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('need to be a string');

        $this->getOptionsResolver(
            new ConnectionDefaultOptionsWithMandatoryConfigurationAndValidator(),
            ['driverClass' => 1]
        );
    }

    public function testConnectionDefaultOptionsWithMandatoryConfigurationAndTwoValidator(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('need to be a string');

        $this->getOptionsResolver(
            new ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoValidator(),
            ['driverClass' => 'foo', 'test' => 9000]
        );
    }

    public function testConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayValidator(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('need to be a string');

        $this->getOptionsResolver(
            new ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayValidator(),
            ['driverClass' => ['connection' => 1]]
        );
    }

    public function testConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayAndTwoValidator(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('need to be a string');

        $this->getOptionsResolver(
            new ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayAndTwoValidator(),
            [
                'driverClass' => [
                    'connection' => 'foo',
                ],
                'orm' => [
                    'default_connection' => 1,
                ],
            ]
        );
    }

    public function testConnectionDefaultOptionsWithMandatoryConfigurationAndStringValidator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [driverClass]; Expected [string], but got [integer], in [' . ConnectionDefaultOptionsWithMandatoryConfigurationAndStringValidator::class . '].');

        $this->getOptionsResolver(
            new ConnectionDefaultOptionsWithMandatoryConfigurationAndStringValidator(),
            ['driverClass' => 1]
        );
    }

    public function testDontValidatedDefaultConfigurationFixture(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getOptionsResolver(
            new DontValidatedDefaultConfigurationFixture(),
            [
                'vendor' => [
                    'package' => [
                        'maxLength' => 'foo',
                    ],
                ],
            ]
        );
    }

    public function testConnectionComponentDefaultOptionsWithMandatoryConfigurationAndStringValidator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [driverClass]; Expected [string], but got [integer], in [Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionsWithMandatoryConfigurationAndStringValidator].');

        $this->getOptionsResolver(
            new ConnectionComponentDefaultOptionsWithMandatoryConfigurationAndStringValidator(),
            [
                'vendor' => [
                    'package' => [
                        'driverClass' => 1,
                    ],
                ],
            ]
        );
    }

    public function testConnectionComponentDefaultOptionsWithDeprecationKeyConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid deprecation message value provided for [params]; Expected [string], but got [NULL], in [' . ConnectionComponentDefaultOptionWithDeprecationKeyAndInvalidMessageConfiguration::class . '].');

        $this->getOptionsResolver(
            new ConnectionComponentDefaultOptionWithDeprecationKeyAndInvalidMessageConfiguration(),
            [
                'doctrine' => [
                    'connection' => [],
                ],
            ]
        );
    }

    public function testConnectionComponentDefaultOptionsWithDeprecationKeyAndEmptyMessageConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Deprecation message cant be empty, for option [params], in [' . ConnectionComponentDefaultOptionWithDeprecationKeyAndEmptyMessageConfiguration::class . '].');

        $this->getOptionsResolver(
            new ConnectionComponentDefaultOptionWithDeprecationKeyAndEmptyMessageConfiguration(),
            [
                'doctrine' => [
                    'connection' => [],
                ],
            ]
        );
    }

    public function testConnectionComponentWithNotFoundDeprecationKeyConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Option [params] cant be deprecated, because it does not exist, in [' . ConnectionComponentWithNotFoundDeprecationKeyConfiguration::class . '].');

        $this->getOptionsResolver(
            new ConnectionComponentWithNotFoundDeprecationKeyConfiguration(),
            [
                'doctrine' => [
                    'connection' => [],
                ],
            ]
        );
    }

    /**
     * @dataProvider provideDeprecationMessagesCases
     *
     * @param string     $class
     * @param null|array $expectedError
     * @param array      $options
     * @param string     $id
     */
    public function testDeprecationMessages(
        string $class,
        ?array $expectedError,
        ?array $options = null,
        ?string $id = null
    ): void {
        \error_clear_last();
        \set_error_handler(static function () {
            return false;
        });

        $e = \error_reporting(0);

        $this->getOptionsResolver(
            new $class(),
            $options ?? ['doctrine' => ['connection' => []]],
            $id
        );

        \error_reporting($e);
        \restore_error_handler();

        $lastError = \error_get_last();

        unset($lastError['file'], $lastError['line']);

        self::assertSame($expectedError, $lastError);
    }

    public function provideDeprecationMessagesCases(): iterable
    {
        return [
            'It deprecates an option with default message' => [
                ConnectionComponentDefaultOptionWithDeprecationKeyConfiguration::class,
                [
                    'type' => \E_USER_DEPRECATED,
                    'message' => 'The option [params] is deprecated.',
                ],
            ],
            'It deprecates an option with custom message' => [
                ConnectionComponentDefaultOptionWithDeprecationKeyAndMessageConfiguration::class,
                [
                    'type' => \E_USER_DEPRECATED,
                    'message' => 'Option [params].',
                ],
            ],
            'It deprecates an mandatory option' => [
                ConnectionComponentDefaultOptionMandatoryContainedIdWithDeprecationKeyConfiguration::class,
                [
                    'type' => \E_USER_DEPRECATED,
                    'message' => 'The option [driverClass] is deprecated.',
                ],
                [
                    'doctrine' => [
                        'connection' => [
                            'orm_default' => [
                                'driverClass' => 'test',
                            ],
                        ],
                    ],
                ],
                'orm_default',
            ],
            'It ignores deprecation if interface is not added' => [
                PlainComponentConfiguration::class,
                null,
            ],
            '' => [
                ConnectionComponentDefaultOptionWithMultiDimensionalDeprecationKeyConfiguration::class,
                [
                    'type' => \E_USER_DEPRECATED,
                    'message' => 'The option [host] is deprecated.',
                ],
            ],
        ];
    }

    public function configDataProvider(): iterable
    {
        return [
            [$this->getTestConfig()],
        ];
    }

    protected function getOptionsResolver($class, $data, ?string $id = null): array
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
        return require __DIR__ . \DIRECTORY_SEPARATOR . 'Fixture' . \DIRECTORY_SEPARATOR . 'testing.config.php';
    }
}
