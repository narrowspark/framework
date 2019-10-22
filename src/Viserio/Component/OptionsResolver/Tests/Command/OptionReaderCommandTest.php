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

namespace Viserio\Component\OptionsResolver\Tests\Command;

use Symfony\Component\VarExporter\VarExporter;
use Viserio\Component\Console\Tester\CommandTestCase;
use Viserio\Component\OptionsResolver\Command\OptionReaderCommand;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionsConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionComponentDefaultOptionsMandatoryContainedIdConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsConfiguration;
use Viserio\Component\OptionsResolver\Tests\Fixture\ConnectionDefaultOptionsWithMandatoryConfigurationAndTwoLevelArrayValidator;

/**
 * @internal
 *
 * @small
 */
final class OptionReaderCommandTest extends CommandTestCase
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
        $commandTester = $this->executeCommand(new OptionReaderCommand(), ['class' => $class]);

        self::assertSame(
            \str_replace("\r\n", "\n", "Output array:\n\n" . VarExporter::export($output)),
            \str_replace("\r\n", "\n", \trim($commandTester->getDisplay(true)))
        );
    }

    public function provideReadCases(): iterable
    {
        return [
            [
                ConnectionDefaultOptionsConfiguration::class,
                [
                    'params' => [
                        'host' => 'awesomehost',
                        'port' => '4444',
                    ],
                ],
            ],
            [
                ConnectionComponentDefaultOptionsConfiguration::class,
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
                ConnectionComponentDefaultOptionsMandatoryContainedIdConfiguration::class,
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
