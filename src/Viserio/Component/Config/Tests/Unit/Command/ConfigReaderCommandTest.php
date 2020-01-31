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

namespace Viserio\Component\Config\Tests\Unit\Command;

use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Config\Command\ConfigReaderCommand;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionComponentDefaultConfigMandatoryContainedIdConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultConfigConfiguration;
use Viserio\Component\Config\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayValidator;
use Viserio\Component\Console\Tester\CommandTestCase;

/**
 * @internal
 *
 * @covers \Viserio\Component\Config\Command\ConfigReaderCommand
 *
 * @small
 */
final class ConfigReaderCommandTest extends CommandTestCase
{
    /**
     * @dataProvider provideReadCases
     *
     * @param string $class
     * @param array  $output
     *
     * @return void
     */
    public function testRead(string $class, array $output): void
    {
        $commandTester = $this->executeCommand(new ConfigReaderCommand(), ['class' => $class]);

        self::assertSame(
            \str_replace("\r\n", "\n", "Output array:\n\n" . VarExporter::export($output)),
            \str_replace("\r\n", "\n", \trim($commandTester->getDisplay(true)))
        );
    }

    public static function provideReadCases(): iterable
    {
        return [
            [
                ConnectionDefaultConfigConfiguration::class,
                [
                    'params' => [
                        'host' => 'awesomehost',
                        'port' => '4444',
                    ],
                ],
            ],
            [
                ConnectionComponentDefaultConfigConfiguration::class,
                [
                    'doctrine' => [
                        'connection' => [
                            'params' => [
                                'host' => 'awesomehost',
                                'port' => '4444',
                            ],
                        ],
                    ],
                ],
            ],
            [
                ConnectionComponentConfiguration::class,
                [
                    'doctrine' => [
                        'connection' => [],
                    ],
                ],
            ],
            [
                ConnectionComponentDefaultConfigMandatoryContainedIdConfiguration::class,
                [
                    'doctrine' => [
                        'connection' => [
                            'params' => [
                                'host' => 'awesomehost',
                                'port' => '4444',
                            ],
                            'driverClass' => null,
                        ],
                    ],
                ],
            ],
            [
                self::class,
                [],
            ],
            [
                ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayValidator::class,
                [
                    'params' => [
                        'host' => 'awesomehost',
                        'port' => '4444',
                    ],
                    'driverClass' => [
                        'connection' => null,
                    ],
                ],
            ],
        ];
    }
}
