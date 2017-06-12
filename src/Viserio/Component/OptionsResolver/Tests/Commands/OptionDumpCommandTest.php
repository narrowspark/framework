<?php
declare(strict_types=1);
namespace Viserio\Component\OptionsResolver\Tests\Commands;

use Narrowspark\TestingHelper\ArrayContainer;
use Narrowspark\TestingHelper\Phpunit\MockeryTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Viserio\Component\OptionsResolver\Commands\OptionDumpCommand;
use Viserio\Component\Parsers\Dumper;

class OptionDumpCommandTest extends MockeryTestCase
{
    public function testCommand()
    {
        $file    = __DIR__ . '/../Fixtures/package.php';
        $command = new OptionDumpCommand();

        $tester = new CommandTester($command);
        $tester->execute(['dir' => __DIR__ . '/../Fixtures'], ['interactive' => false]);
        $tester->getDisplay();

        self::assertEquals(
            [
                'vendor' => [
                    'package' => [
                        'minLength' => 2,
                        'maxLength' => null,
                    ],
                ],
            ],
            include $file
        );

        @unlink($file);
    }

    public function testCommandShowError()
    {
        $file    = __DIR__ . '/../Fixtures/package.php';
        $command = new OptionDumpCommand();

        $tester = new CommandTester($command);
        $tester->execute(['dir' => __DIR__ . '/../Fixtures', '--format' => 'json'], ['interactive' => false]);

        $output = $tester->getDisplay(true);

        self::assertSame("Only the php format is supported; use composer req viserio/parsers to get json, xml, yml output.\n", $output);
    }

    public function testCommandWithDumper()
    {
        $file      = __DIR__ . '/../Fixtures/package.php';
        $container = new ArrayContainer([Dumper::class => new Dumper()]);
        $command   = new OptionDumpCommand();
        $command->setContainer($container);

        $tester = new CommandTester($command);
        $tester->execute(['dir' => __DIR__ . '/../Fixtures'], ['interactive' => false]);
        $tester->getDisplay();

        self::assertEquals(
            [
                'vendor' => [
                    'package' => [
                        'minLength' => 2,
                        'maxLength' => null,
                    ],
                ],
            ],
            include $file
        );

        @unlink($file);
    }
}
