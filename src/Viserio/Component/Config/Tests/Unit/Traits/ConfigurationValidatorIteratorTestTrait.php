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
use Exception;
use IteratorIterator;
use RuntimeException;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultOptionsWithMandatoryConfigurationAndStringValidator;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndStringValidator;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayAndTwoValidator;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayValidator;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoValidator;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndValidator;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryNullValueConfigurationAndStringValidator;
use Viserio\Component\Config\Tests\Fixture\DontValidatedDefaultConfigurationFixture;
use Viserio\Component\Config\Tests\Fixture\InvalidValidatedComponentConfigurationFixture;
use Viserio\Component\Config\Tests\Fixture\ValidatedComponentConfigurationFixture;
use Viserio\Component\Config\Tests\Fixture\ValidatedComponentWithArrayValidatorConfigurationFixture;
use Viserio\Component\Config\Tests\Fixture\ValidatedDimensionalComponentConfigurationFixture;
use Viserio\Component\Config\Tests\Fixture\ValidateDefaultValueOnOverwriteComponentFixture;
use Viserio\Contract\Config\Exception\InvalidArgumentException;
use Viserio\Contract\Config\Exception\InvalidValidatorException;

/**
 * @method IteratorIterator getConfigurationIterator(string $class, \ArrayIterator $iterator, ?string $id = null)
 */
trait ConfigurationValidatorIteratorTestTrait
{
    public function testValidatorResolving(): void
    {
        try {
            $this->getConfigurationIterator(
                ValidatedComponentConfigurationFixture::class,
                new ArrayIterator([
                    'vendor' => [
                        'package' => [
                            'maxLength' => 2,
                        ],
                    ],
                ])
            );

            $this->expectNotToPerformAssertions();
        } catch (Exception $e) {
            self::fail($e->getMessage());
        }
    }

    public function testValidatorResolvingShouldCallGivenValidator(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('need to be a string');

        $this->getConfigurationIterator(
            ConnectionDefaultOptionsWithMandatoryConfigurationAndValidator::class,
            new ArrayIterator(['driverClass' => 1])
        );
    }

    public function testValidatorResolvingShouldSupportMoreThanOneValidator(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('need to be a string');

        $this->getConfigurationIterator(
            ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoValidator::class,
            new ArrayIterator(['driverClass' => 'foo', 'test1' => 9000])
        );
    }

    public function testValidatorResolvingShouldSupportNestedArrayValidating(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('need to be a string');

        $this->getConfigurationIterator(
            ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayValidator::class,
            new ArrayIterator(['driverClass' => ['connection' => 1]])
        );
    }

    public function testValidatorResolvingShouldSupportNestedArrayValidatingWithMoreThanOneValidator(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('need to be a string');

        $this->getConfigurationIterator(
            ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayAndTwoValidator::class,
            new ArrayIterator([
                'driverClass' => [
                    'connection' => 'foo',
                ],
                'orm' => [
                    'default_connection' => 1,
                ],
            ])
        );
    }

    public function testValidatorResolvingShouldCallDefaultStringValidator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [driverClass]; Expected [string], but got [integer], in [' . ConnectionDefaultOptionsWithMandatoryConfigurationAndStringValidator::class . '].');

        $this->getConfigurationIterator(
            ConnectionDefaultOptionsWithMandatoryConfigurationAndStringValidator::class,
            new ArrayIterator(['driverClass' => 1])
        );
    }

    public function testValidatorResolvingShouldSkipValidationOnDefaultConfiguration(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getConfigurationIterator(
            DontValidatedDefaultConfigurationFixture::class,
            new ArrayIterator([
                'vendor' => [
                    'package' => [
                        'maxLength' => 'foo',
                    ],
                ],
            ])
        );
    }

    public function testValidatorResolvingShouldCallStringValidatorWithSupportedDefaultConfigurationAndMandatoryConfiguration(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [driverClass]; Expected [string], but got [integer], in [Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultOptionsWithMandatoryConfigurationAndStringValidator].');

        $this->getConfigurationIterator(
            ConnectionComponentDefaultOptionsWithMandatoryConfigurationAndStringValidator::class,
            new ArrayIterator([
                'vendor' => [
                    'package' => [
                        'driverClass' => 1,
                    ],
                ],
            ])
        );
    }

    public function testValidatorResolvingShouldCallOneExceptionAfterAnother(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [maxLength]; Expected [string] or [int], but got [boolean], in [Viserio\Component\Config\Tests\Fixture\ValidatedComponentWithArrayValidatorConfigurationFixture].');

        $this->getConfigurationIterator(
            ValidatedComponentWithArrayValidatorConfigurationFixture::class,
            new ArrayIterator([
                'vendor' => [
                    'package' => [
                        'maxLength' => true,
                    ],
                ],
            ])
        );
    }

    public function testValidatorResolvingShouldValidateWithDimensionConfiguration(): void
    {
        try {
            $this->getConfigurationIterator(
                ValidatedDimensionalComponentConfigurationFixture::class,
                new ArrayIterator([
                    'vendor' => [
                        'package' => [
                            'maxLength' => 'string',
                            'foo' => [
                                'maxLength' => 1,
                            ],
                        ],
                    ],
                ])
            );

            $this->expectNotToPerformAssertions();
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }

    public function testValidatorResolvingShouldThrowExceptionIfValidationFailed(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getConfigurationIterator(
            ValidatedComponentConfigurationFixture::class,
            new ArrayIterator([
                'vendor' => [
                    'package' => [
                        'maxLength' => 'string',
                    ],
                ],
            ])
        );
    }

    public function testValidatorResolvingShouldThrowValidationExceptionWithDimensionConfiguration(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getConfigurationIterator(
            ValidatedDimensionalComponentConfigurationFixture::class,
            new ArrayIterator([
                'vendor' => [
                    'package' => [
                        'foo' => [
                            'maxLength' => 'string',
                        ],
                    ],
                ],
            ])
        );
    }

    public function testValidatorResolvingShouldThrowExceptionOnInvalidValidator(): void
    {
        $this->expectException(InvalidValidatorException::class);
        $this->expectExceptionMessage('The validator must be of type callable or array<string|object, string>; [string] given, in [Viserio\Component\Config\Tests\Fixture\InvalidValidatedComponentConfigurationFixture].');

        $this->getConfigurationIterator(
            InvalidValidatedComponentConfigurationFixture::class,
            new ArrayIterator([
                'vendor' => [
                    'package' => [
                        'maxLength' => 'string',
                    ],
                ],
            ])
        );
    }

    public function testValidatorResolvingShouldValidateOnOverwrittenDefaultConfiguration(): void
    {
        try {
            $this->getConfigurationIterator(
                ValidateDefaultValueOnOverwriteComponentFixture::class,
                new ArrayIterator([
                    'vendor' => [
                        'package' => [
                            'maxLength' => 20,
                            'minLength' => 10,
                        ],
                    ],
                ])
            );

            $this->expectNotToPerformAssertions();
        } catch (Exception $exception) {
            self::fail($exception->getMessage());
        }
    }

    public function testValidatorResolvingShouldThrowValidationExceptionOnOverwrittenDefaultConfiguration(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Value is not a int.');

        $this->getConfigurationIterator(
            ValidateDefaultValueOnOverwriteComponentFixture::class,
            new ArrayIterator([
                'vendor' => [
                    'package' => [
                        'maxLength' => 20,
                        'minLength' => 'string',
                    ],
                ],
            ])
        );
    }

    public function testValidatorResolvingShouldThrowExceptionOnNullValueIfStringIsRequired(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid configuration value provided for [driverClass]; Expected [string], but got [NULL], in [Viserio\Component\Config\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryNullValueConfigurationAndStringValidator].');

        $this->getConfigurationIterator(
            ConnectionDefaultOptionsWithMandatoryNullValueConfigurationAndStringValidator::class,
            new ArrayIterator([
                'driverClass' => null,
            ])
        );
    }
}
