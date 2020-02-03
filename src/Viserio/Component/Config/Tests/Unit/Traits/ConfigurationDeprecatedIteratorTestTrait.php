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
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigMandatoryContainedIdWithDeprecationKeyConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigWithDeprecationKeyAndEmptyMessageConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigWithDeprecationKeyAndInvalidMessageConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigWithDeprecationKeyAndMessageConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigWithDeprecationKeyConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigWithMultiDimensionalDeprecationKeyConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentWithNotFoundDeprecationKeyConfiguration;
use Viserio\Contract\Config\Exception\InvalidArgumentException;

/**
 * @method IteratorIterator getConfigurationIterator(string $class, \ArrayIterator $iterator, ?string $id = null)
 */
trait ConfigurationDeprecatedIteratorTestTrait
{
    public function testDeprecatedMessageResolvingShouldThrowExceptionOnEmptyDeprecationMessage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Deprecation message cant be empty, for config key [params], in [' . ConnectionComponentDefaultConfigWithDeprecationKeyAndEmptyMessageConfiguration::class . '].');

        $this->getConfigurationIterator(
            ConnectionComponentDefaultConfigWithDeprecationKeyAndEmptyMessageConfiguration::class,
            new ArrayIterator([
                'doctrine' => [
                    'connection' => [],
                ],
            ])
        );
    }

    public function testDeprecatedMessageResolvingShouldThrowExceptionOnNullDeprecationMessage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid deprecation message value provided for [params]; Expected [string], but got [NULL], in [' . ConnectionComponentDefaultConfigWithDeprecationKeyAndInvalidMessageConfiguration::class . '].');

        $this->getConfigurationIterator(
            ConnectionComponentDefaultConfigWithDeprecationKeyAndInvalidMessageConfiguration::class,
            new ArrayIterator([
                'doctrine' => [
                    'connection' => [],
                ],
            ])
        );
    }

    /**
     * @dataProvider provideDeprecationMessageResolvingCases
     *
     * @param string     $class
     * @param null|array $expectedError
     * @param array      $config
     * @param string     $id
     */
    public function testDeprecatedMessageResolving(
        string $class,
        ?array $expectedError,
        ?array $config = null,
        ?string $id = null
    ): void {
        \error_clear_last();
        \set_error_handler(static function () {
            return false;
        });

        $e = \error_reporting(0);

        $this->getConfigurationIterator(
            $class,
            new ArrayIterator($config ?? ['doctrine' => ['connection' => []]]),
            $id
        );

        \error_reporting($e);
        \restore_error_handler();

        $lastError = \error_get_last();

        unset($lastError['file'], $lastError['line']);

        self::assertSame($expectedError, $lastError);
    }

    public static function provideDeprecationMessageResolvingCases(): iterable
    {
        return [
            'It deprecates an config key with default message' => [
                ConnectionComponentDefaultConfigWithDeprecationKeyConfiguration::class,
                [
                    'type' => \E_USER_DEPRECATED,
                    'message' => 'The config key [params] is deprecated.',
                ],
            ],
            'It deprecates an config key with custom message' => [
                ConnectionComponentDefaultConfigWithDeprecationKeyAndMessageConfiguration::class,
                [
                    'type' => \E_USER_DEPRECATED,
                    'message' => 'Configuration [params].',
                ],
            ],
            'It deprecates an mandatory config key' => [
                ConnectionComponentDefaultConfigMandatoryContainedIdWithDeprecationKeyConfiguration::class,
                [
                    'type' => \E_USER_DEPRECATED,
                    'message' => 'The config key [driverClass] is deprecated.',
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
            '' => [
                ConnectionComponentDefaultConfigWithMultiDimensionalDeprecationKeyConfiguration::class,
                [
                    'type' => \E_USER_DEPRECATED,
                    'message' => 'The config key [host] is deprecated.',
                ],
            ],
        ];
    }

    public function testDeprecatedMessageResolvingShouldThrowAExceptionIfDeprecatedKeyWasNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Config key [params] cant be deprecated, because it does not exist, in [' . ConnectionComponentWithNotFoundDeprecationKeyConfiguration::class . '].');

        $this->getConfigurationIterator(
            ConnectionComponentWithNotFoundDeprecationKeyConfiguration::class,
            new ArrayIterator([
                'doctrine' => [
                    'connection' => [],
                ],
            ])
        );
    }
}
