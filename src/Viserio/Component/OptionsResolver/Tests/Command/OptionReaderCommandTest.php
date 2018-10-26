<?php
declare(strict_types=1);
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
 */
final class OptionReaderCommandTest extends CommandTestCase
{
    /**
     * @dataProvider optionsDataprovider
     *
     * @param string $class
     * @param array  $output
     *
     * @return void
     */
    public function testRead(string $class, array $output): void
    {
        $commandTester = $this->executeCommand(new OptionReaderCommand(), ['class' => $class]);

        $this->assertSame(
            \str_replace("\r\n", "\n", 'Output array:' . \PHP_EOL . \PHP_EOL . VarExporter::export($output)),
            \str_replace("\r\n", "\n", \trim($commandTester->getDisplay(true)))
        );
    }

    public function optionsDataprovider(): array
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
